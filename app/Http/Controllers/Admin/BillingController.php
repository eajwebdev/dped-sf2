<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /** Payment history across all teachers, newest first. */
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $payments = SubscriptionPayment::with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.billing.index', [
            'payments' => $payments,
            'status' => $request->get('status'),
        ]);
    }
}
