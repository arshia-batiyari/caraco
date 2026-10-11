<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminList
{
    public static function paginate(Builder $query, Request $request, ?string $translationField = null): LengthAwarePaginator
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $fillable = $model->getFillable();
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) > 80) {
            $term = mb_substr($term, 0, 80);
        }

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            if ($translationField !== null) {
                $query->whereHas('translations', function (Builder $inner) use ($translationField, $like) {
                    $inner->where($translationField, 'like', $like);
                });
            } else {
                $query->where(function (Builder $inner) use ($fillable, $table, $like) {
                    $used = false;
                    foreach (['name', 'email', 'title', 'message'] as $column) {
                        if (! in_array($column, $fillable, true)) {
                            continue;
                        }
                        $used ? $inner->orWhere($table.'.'.$column, 'like', $like) : $inner->where($table.'.'.$column, 'like', $like);
                        $used = true;
                    }
                });
            }
        }

        if (in_array((string) $request->query('published'), ['0', '1'], true) && in_array('is_published', $fillable, true)) {
            $query->where($table.'.is_published', $request->query('published') === '1');
        }

        if ($request->filled('status') && in_array('status', $fillable, true)) {
            $status = (string) $request->query('status');
            if (preg_match('/^[a-z_]{2,20}$/', $status) === 1) {
                $query->where($table.'.status', $status);
            }
        }

        $default = in_array('position', $fillable, true) ? 'position' : (in_array('name', $fillable, true) ? 'name' : 'created_at');
        $sort = (string) $request->query('sort', $default);
        $allowed = ['position', 'updated_at', 'created_at', 'id', 'name'];
        if (! in_array($sort, $allowed, true) || ($sort === 'position' && ! in_array('position', $fillable, true)) || ($sort === 'name' && ! in_array('name', $fillable, true))) {
            $sort = $default;
        }
        $direction = $request->query('dir') === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, ['created_at', 'updated_at'], true) && $request->query('dir') !== 'asc') {
            $direction = 'desc';
        }

        return $query->orderBy($table.'.'.$sort, $direction)->orderBy($table.'.id')->paginate(20)->withQueryString();
    }
}
