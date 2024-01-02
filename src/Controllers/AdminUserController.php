<?php

namespace Slowlyo\OwlAdmin\Controllers;

use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Operation;
use Slowlyo\OwlAdmin\Services\AdminUserService;
use Slowlyo\OwlAdmin\Services\AdminRoleService;

/**
 * @property AdminUserService $service
 */
class AdminUserController extends AdminController
{
    protected string $serviceName = AdminUserService::class;

    public function list(): Page
    {
        $model = $this->serviceName::make()->getModel();
        $columns = [
            amis()->TableColumn('id', 'ID')->sortable(),
            amis()->TableColumn('avatar', __('admin.admin_user.avatar'))->type('avatar')->src('${avatar}'),
            amis()->TableColumn('username', __('admin.username')),
            amis()->TableColumn('mobile', __('admin.admin_user.mobile'))->copyable(),
            amis()->TableColumn('email', __('admin.admin_user.email'))->copyable(),
            amis()->TableColumn('name', __('admin.admin_user.name')),
            amis()->TableColumn('gender', __('admin.admin_user.gender'))->type('status')->source($model::toSource('genderOpt')),
            amis()->TableColumn('birthday', __('admin.admin_user.birthday'))->type('date'),
            amis()->TableColumn('roles', __('admin.admin_user.roles'))->type('each')->items(
                amis()->Tag()->label('${name}')->className('my-1')
            ),
            amis()->TableColumn('state', __('admin.admin_user.state'))->type('status')->source($model::toSource('stateOpt')),
            amis()->TableColumn('reason', __('admin.admin_user.reason')),
            amis()->TableColumn('memo', __('admin.admin_user.memo')),
            amis()->TableColumn('created_at', __('admin.created_at'))->type('datetime')->sortable(true),
            amis()->TableColumn('updated_at', __('admin.updated_at'))->type('datetime'),
            Operation::make()->label(__('admin.actions'))->buttons([
                $this->rowEditButton(true),
                $this->rowDeleteButton()->visibleOn('this.id != 1'),
            ]),
        ];

        $crud = $this->baseCRUD()
            ->filterTogglable(true)
            ->filter($this->baseFilter()->body(
                amis()->TextControl('keyword', __('admin.keyword'))
                    ->size('lg')
                    ->placeholder(__('admin.admin_user.search_username'))
            ))
            ->columns($columns);
        return $this->baseList($crud);
    }

    public function form($isEdit = false): Form
    {
        $model = $this->serviceName::make()->getModel();
        $form = [
            amis()->ImageControl('avatar', __('admin.admin_user.avatar'))->receiver($this->uploadImagePath()),
            amis()->TextControl('username', __('admin.username'))->required(),
            amis()->TextControl('name', __('admin.admin_user.name'))->required(),
            amis()->TextControl('password', __('admin.password'))->type('input-password'),
            amis()->TextControl('confirm_password', __('admin.confirm_password'))->type('input-password'),
            amis()->SelectControl('roles', __('admin.admin_user.roles'))
                ->searchable()
                ->multiple()
                ->labelField('name')
                ->valueField('id')
                ->joinValues(false)
                ->extractValue()
                ->options(AdminRoleService::make()->query()->get(['id', 'name'])),
            amis()->TextControl('mobile', __('admin.admin_user.mobile'))->type('input-number')->validations('isPhoneNumber'),
            amis()->TextControl('email', __('admin.admin_user.email'))->type('input-email')->validations('isEmail'),
            amis()->DateControl('birthday', __('admin.admin_user.birthday'))->format("YYYY-MM-DD"),
            amis()->RadiosControl('gender', __('admin.admin_user.gender'))->options($model::$genderOpt)->inline(true)->value($model::$genderDef),
            amis()->RadiosControl('state', __('admin.admin_user.state'))->options($model::$stateOpt)->inline(true)->value($model::$stateDef)->required(),
            amis()->TextControl('reason', __('admin.admin_user.reason'))->visibleOn('this.state != 1')->required(),
            amis()->TextControl('memo', __('admin.admin_user.memo')),
        ];

        return $this->baseForm()->body($form);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
