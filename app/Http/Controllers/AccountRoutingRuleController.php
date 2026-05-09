<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRoutingRuleRequest;
use App\Http\Requests\UpdateAccountRoutingRuleRequest;
use App\Http\Resources\AccountRoutingRuleCollection;
use App\Http\Resources\AccountRoutingRuleResource;
use App\Models\AccountRoutingRule;
use App\Support\Pagination;
use Illuminate\Http\Request;

class AccountRoutingRuleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = Pagination::resolvePerPage($request, default: 50, max: 100);

        return new AccountRoutingRuleCollection(
            AccountRoutingRule::orderBy('sort_order')
                ->orderBy('id')
                ->with('account.category')
                ->paginate($perPage)
        );
    }

    public function store(StoreAccountRoutingRuleRequest $request)
    {
        $rule = AccountRoutingRule::create($request->validated());
        $rule->load('account.category');

        return response()->json([
            'account_routing_rule' => new AccountRoutingRuleResource($rule),
        ], 201);
    }

    public function show(string $id)
    {
        return new AccountRoutingRuleResource(
            AccountRoutingRule::with('account.category')->findOrFail($id)
        );
    }

    public function update(UpdateAccountRoutingRuleRequest $request, string $id)
    {
        $rule = AccountRoutingRule::findOrFail($id);
        $rule->update($request->validated());
        $rule->load('account.category');

        return response()->json([
            'account_routing_rule' => new AccountRoutingRuleResource($rule),
        ], 200);
    }

    public function destroy(string $id)
    {
        $rule = AccountRoutingRule::findOrFail($id);
        $rule->delete();

        return response()->noContent();
    }
}
