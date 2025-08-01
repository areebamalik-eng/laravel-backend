<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    // ✅ Show own + shared expenses for same group
    public function index()
    {
        $groupCode = auth()->user()->group_code;
        $userId = auth()->id();

        $expenses = Expense::with('user')
            ->where(function ($query) use ($groupCode, $userId) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($subQuery) use ($groupCode, $userId) {
                        $subQuery->where('is_shared', true)
                            ->whereHas('user', function ($q) use ($groupCode, $userId) {
                                $q->where('group_code', $groupCode)
                                    ->where('id', '!=', $userId);
                            });
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($expenses, 200);
    }

    // ✅ Store new expense (with is_shared + notifications)
    public function store(Request $request)
    {
        Log::info('📥 Received expense data:', $request->all());

        $validatedData = $request->validate([
            'title'     => 'required|string|max:255',
            'amount'    => 'required|numeric',
            'paid_by'   => 'required|string|max:255',
            'date'      => 'required|date',
            'category'  => 'nullable|string|max:255',
            'note'      => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        try {
            $validatedData['user_id'] = auth()->id();
            $validatedData['group_code'] = auth()->user()->group_code;
            $validatedData['is_shared'] = $request->is_shared ?? false;

            $expense = Expense::create($validatedData);
            $expense->load('user');

            Log::info('✅ Expense created:', $expense->toArray());

            // ✅ Send notification to other group members if shared
            if ($expense->is_shared) {
                $groupCode = auth()->user()->group_code;
                $senderName = auth()->user()->name;

                $members = User::where('group_code', $groupCode)
                    ->where('id', '!=', auth()->id())
                    ->get();

                foreach ($members as $member) {
                    Notification::create([
                        'user_id'     => $member->id,
                        'type'        => 'expense',
                        'resource_id' => $expense->id,
                        'message'     => "$senderName added a shared expense: {$expense->title}",
                        'is_read'     => false,
                    ]);
                }
            }

            return response()->json($expense, 201);
        } catch (\Exception $e) {
            Log::error('❌ Failed to create expense:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save expense'], 500);
        }
    }

    // ✅ Update expense
    public function update(Request $request, $id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        $validatedData = $request->validate([
            'title'     => 'required|string|max:255',
            'amount'    => 'required|numeric',
            'paid_by'   => 'required|string|max:255',
            'date'      => 'required|date',
            'category'  => 'nullable|string|max:255',
            'note'      => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $expense->update($validatedData);

        return response()->json($expense->fresh('user'), 200);
    }

    // ✅ Delete expense
    public function destroy($id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        $expense->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }
}
