<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChartAccountRequest;
use App\Models\ChartAccount;
use Illuminate\Http\Request;

class ChartAccountController extends Controller
{
    public function index()
    {
        $accounts = ChartAccount::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                });
            })
            ->when(request('parent_id'), function ($query, $parentId) {
                $query->where('parent_id', $parentId);
            }, function ($query) {
                $query->whereNull('parent_id');
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        $parents = [];

        if (request('parent_id')) {
            $currentParent = ChartAccount::find(request('parent_id'));
            if ($currentParent) {
                $parents[] = $currentParent;

                $parent = $currentParent->parent;

                while ($parent) {
                    array_unshift($parents, $parent);
                    $parent = $parent->parent;
                }
            }
        }

        return inertia('ChartAccounts/Index', [
            'accounts' => $accounts,
            'parents' => $parents,
            'currentParentId' => request('parent_id'),
            'filters' => request()->only(['search', 'parent_id']),
        ]);
    }

    public function create()
    {
        $potentialParents = ChartAccount::where('active', true)
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'level']);

        return inertia('ChartAccounts/Create', [
            'potentialParents' => $potentialParents,
            'preselectedParentId' => request('parent_id'),
        ]);
    }

    public function store(ChartAccountRequest $request)
    {
        $account = ChartAccount::create($request->validated());

        $redirectParams = [];
        if ($account->parent_id) {
            $redirectParams['parent_id'] = $account->parent_id;
        }

        return to_route('chart-accounts.index', $redirectParams);
    }

    public function edit(ChartAccount $chartAccount)
    {
        $potentialParents = ChartAccount::where('active', true)
            ->where('id', '!=', $chartAccount->id)
            ->whereNotIn('id', $this->getAllChildrenIds($chartAccount))
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'level']);

        return inertia('ChartAccounts/Edit', [
            'account' => $chartAccount,
            'potentialParents' => $potentialParents,
            'hasChildren' => $chartAccount->children()->count() > 0,
        ]);
    }

    public function update(ChartAccountRequest $request, ChartAccount $chartAccount)
    {
        $oldParentId = $chartAccount->parent_id;

        $chartAccount->update($request->validated());

        if ($oldParentId != $chartAccount->parent_id) {
            if ($oldParentId) {
                $this->reorderSiblingCodes($oldParentId);
            }

            $this->updateAccountCode($chartAccount);
            $this->updateChildrenCodes($chartAccount);
        }

        if ($request->has('default_receivable') && $request->default_receivable) {
            ChartAccount::where('id', '!=', $chartAccount->id)
                ->where('default_receivable', true)
                ->update(['default_receivable' => false]);
        }

        if ($request->has('default_purchase') && $request->default_purchase) {
            ChartAccount::where('id', '!=', $chartAccount->id)
                ->where('default_purchase', true)
                ->update(['default_purchase' => false]);
        }

        $redirectParams = [];
        if ($chartAccount->parent_id) {
            $redirectParams['parent_id'] = $chartAccount->parent_id;
        }

        return to_route('chart-accounts.index', $redirectParams);
    }

    public function destroy(ChartAccount $chartAccount)
    {
        if ($chartAccount->children()->count() > 0) {
            abort(403, 'Não é possível excluir uma conta que possui subcontas');
        }

        $parentId = $chartAccount->parent_id;
        $chartAccount->delete();

        if ($parentId) {
            $this->reorderSiblingCodes($parentId);
        }

        $redirectParams = [];
        if ($parentId) {
            $redirectParams['parent_id'] = $parentId;
        }

        return to_route('chart-accounts.index', $redirectParams);
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $accounts = ChartAccount::whereIn('id', $ids)
                ->get(['id', 'name', 'code']);

            return response()->json([
                'data' => $accounts,
            ]);
        }

        $query = $request->search ?? '';
        $onlyAllowsTransactions = $request->boolean('only_allows_transactions', true);

        $accounts = ChartAccount::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('code', 'ilike', "%{$query}%");
            })
            ->when($onlyAllowsTransactions, function ($q) {
                $q->where('allows_transactions', true);
            })
            ->where('active', true)
            ->orderBy('code')
            ->limit(10)
            ->get(['id', 'name', 'code']);

        return response()->json([
            'data' => $accounts,
        ]);
    }

    private function updateAccountCode(ChartAccount $account)
    {
        if ($account->parent_id) {
            $parent = ChartAccount::find($account->parent_id);

            $siblings = ChartAccount::where('parent_id', $parent->id)
                ->where('id', '!=', $account->id)
                ->orderBy('code')
                ->get();

            $used_segments = [];
            foreach ($siblings as $sibling) {
                $segments = explode('.', $sibling->code);
                $currentSegment = (int) end($segments);
                $used_segments[$currentSegment] = true;
            }

            $next_segment = 1;
            while (isset($used_segments[$next_segment])) {
                $next_segment++;
            }

            $newCode = $parent->code . '.' . $next_segment;

            $account->code = $newCode;
            $account->level = $parent->level + 1;
            $account->save();
        } else {
            $rootAccounts = ChartAccount::whereNull('parent_id')
                ->where('id', '!=', $account->id)
                ->orderBy('code')
                ->get();

            $used_codes = [];
            foreach ($rootAccounts as $rootAccount) {
                $used_codes[(int) $rootAccount->code] = true;
            }

            $next_code = 1;
            while (isset($used_codes[$next_code])) {
                $next_code++;
            }

            $account->code = (string) $next_code;
            $account->level = 1;
            $account->save();
        }
    }

    private function reorderSiblingCodes($parentId)
    {
        if (! $parentId) {
            $siblings = ChartAccount::whereNull('parent_id')
                ->orderBy('code')
                ->get();

            $index = 1;
            foreach ($siblings as $sibling) {
                if ((int) $sibling->code !== $index) {
                    $sibling->code = (string) $index;
                    $sibling->save();

                    $this->updateChildrenCodes($sibling);
                }
                $index++;
            }
        } else {
            $parent = ChartAccount::find($parentId);
            if (! $parent) {
                return;
            }

            $siblings = ChartAccount::where('parent_id', $parentId)
                ->orderBy('code')
                ->get();

            $index = 1;
            foreach ($siblings as $sibling) {
                $segments = explode('.', $sibling->code);
                $currentSegment = (int) end($segments);

                if ($currentSegment !== $index) {
                    $segments[count($segments) - 1] = (string) $index;
                    $newCode = implode('.', $segments);

                    $sibling->code = $newCode;
                    $sibling->save();

                    $this->updateChildrenCodes($sibling);
                }
                $index++;
            }
        }
    }

    private function getAllChildrenIds(ChartAccount $account)
    {
        $childrenIds = [];

        foreach ($account->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $this->getAllChildrenIds($child));
        }

        return $childrenIds;
    }

    private function updateChildrenCodes(ChartAccount $account)
    {
        foreach ($account->children as $child) {
            $segments = explode('.', $account->code);
            $childSegments = explode('.', $child->code);
            $lastChildSegment = end($childSegments);

            $newCode = $account->code . '.' . $lastChildSegment;
            $child->code = $newCode;
            $child->level = $account->level + 1;
            $child->save();

            $this->updateChildrenCodes($child);
        }
    }
}
