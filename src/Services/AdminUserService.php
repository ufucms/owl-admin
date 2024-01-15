<?php

namespace Slowlyo\OwlAdmin\Services;

use Illuminate\Support\Arr;
use Slowlyo\OwlAdmin\Admin;
use Illuminate\Support\Facades\Hash;
use Slowlyo\OwlAdmin\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method AdminUser getModel()
 * @method AdminUser|Builder query()
 */
class AdminUserService extends AdminService
{
    public function __construct()
    {
        $this->modelName = Admin::adminUserModel();
    }

    public function getEditData($id): Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        $adminUser = parent::getEditData($id)->makeHidden('password');

        $adminUser->load('roles');

        return $adminUser;
    }

    public function store($data): bool
    {
        $this->checkUsernameUnique($data['username']);

        amis_abort_if(!data_get($data, 'password'), __('admin.required', ['attribute' => __('admin.password')]));

        $this->passwordHandler($data);
        $this->stateReasonHandler($data);

        $columns = $this->getTableColumns();

        $model = $this->getModel();

        return $this->saveData($data, $columns, $model);
    }

    public function update($primaryKey, $data): bool
    {
        $this->checkUsernameUnique($data['username'], $primaryKey);
        $this->passwordHandler($data);
        $this->stateReasonHandler($data);

        $columns = $this->getTableColumns();

        $model = $this->query()->whereKey($primaryKey)->first();

        return $this->saveData($data, $columns, $model);
    }

    public function checkUsernameUnique($username, $id = 0)
    {
        $exists = $this->query()
            ->where('username', $username)
            ->when($id, fn($query) => $query->where('id', '<>', $id))
            ->exists();

        amis_abort_if($exists, __('admin.admin_user.username_already_exists'));
    }

    public function updateUserSetting($primaryKey, $data): bool
    {
        $this->passwordHandler($data, $primaryKey);

        return parent::update($primaryKey, $data);
    }

    public function passwordHandler(&$data, $id = null)
    {
        $password = Arr::get($data, 'password');

        if ($password) {
            amis_abort_if($password !== Arr::get($data, 'confirm_password'), __('admin.admin_user.password_confirmation'));

            if ($id) {
                amis_abort_if(!Arr::get($data, 'old_password'), __('admin.admin_user.old_password_required'));

                $oldPassword = $this->query()->where('id', $id)->value('password');

                amis_abort_if(!Hash::check($data['old_password'], $oldPassword), __('admin.admin_user.old_password_error'));
            }

            $data['password'] = bcrypt($password);

            unset($data['confirm_password']);
            unset($data['old_password']);
        }
    }

    public function stateReasonHandler(&$data)
    {
        $state = Arr::get($data, 'state');
        if ($state) {
            $data['reason'] = '';
        }
    }

    public function list()
    {
        $keyword = request()->keyword;

        $query = $this->query()
            ->with('roles')
            ->select(['id', 'username', 'mobile', 'name', 'gender', 'birthday', 'email', 'state', 'avatar', 'reason', 'created_at', 'updated_at'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('username', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%")->orWhere('mobile', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%");
            });

        $this->sortable($query);

        $list  = $query->paginate(request()->input('perPage', 20));
        $items = $list->items();
        $total = $list->total();

        return compact('items', 'total');
    }

    /**
     * @param           $data
     * @param array     $columns
     * @param AdminUser $model
     *
     * @return bool
     */
    protected function saveData($data, array $columns, AdminUser $model): bool
    {
        $roles = Arr::pull($data, 'roles');

        foreach ($data as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }

            $model->setAttribute($k, $v);
        }

        if ($model->save()) {
            $model->roles()->sync(Arr::has($roles, '0.id') ? Arr::pluck($roles, 'id') : $roles);

            return true;
        }

        return false;
    }

    /**
     * 删除
     *
     * @param string $ids
     *
     * @return mixed
     */
    public function delete(string $ids): mixed
    {
        $idsArr = explode(',', $ids);
        $delectIds = array_values(array_diff($idsArr, [1]));//禁止删除超级管理员
        amis_abort_if(empty($delectIds), __('admin.action_failed'));
        return $this->query()->whereIn($this->primaryKey(), $delectIds)->delete();
    }
}
