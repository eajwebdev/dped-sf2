<?php

namespace App\Console\Commands;

use App\Services\SchoolMasterlistImportService;
use Illuminate\Console\Command;

class ImportSchoolMasterlist extends Command
{
    protected $signature = 'schools:import-masterlist
        {file=public/masterlist_of_schools_based_on_school_year_-_original.xls : Workbook path}
        {--sheet=FOR IMPORT : Sheet that contains database-ready rows}
        {--refresh-import-sheet : Rebuild FOR IMPORT from Worksheet before importing}
        {--source-sheet=Worksheet : Raw source sheet used when refreshing FOR IMPORT}
        {--import-sheet=FOR IMPORT : Normalized import sheet to refresh}';

    protected $description = 'Import or update schools from the DepEd masterlist workbook.';

    public function handle(SchoolMasterlistImportService $importer): int
    {
        $file = (string) $this->argument('file');
        $path = $this->resolvePath($file);
        $sheet = (string) $this->option('sheet');

        if ($this->option('refresh-import-sheet')) {
            $prepared = $importer->populateImportSheet(
                $path,
                (string) $this->option('source-sheet'),
                (string) $this->option('import-sheet'),
            );

            $sheet = $prepared['sheet'];

            $this->components->info(sprintf(
                'Prepared [%s] from [%s]: %d rows, %d skipped.',
                $prepared['sheet'],
                $prepared['source_sheet'],
                $prepared['rows'],
                $prepared['skipped'],
            ));
        }

        $result = $importer->import($path, $sheet);

        $this->components->info(sprintf(
            'Imported %d schools from [%s]: %d created, %d updated, %d skipped.',
            $result['total'],
            $result['sheet'],
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        if ($result['source_school_year']) {
            $this->line('Source school year: '.$result['source_school_year']);
        }

        return self::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        if (is_file($file)) {
            return $file;
        }

        $basePath = base_path($file);

        return is_file($basePath) ? $basePath : $file;
    }
}
