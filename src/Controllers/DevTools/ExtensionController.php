<?php

namespace Slowlyo\OwlAdmin\Controllers\DevTools;

use Slowlyo\OwlAdmin\Admin;
use Illuminate\Http\Request;
use Slowlyo\OwlAdmin\Events\ExtensionChanged;
use Slowlyo\OwlAdmin\Renderers\Tpl;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Alert;
use Slowlyo\OwlAdmin\Renderers\Dialog;
use Slowlyo\OwlAdmin\Renderers\Drawer;
use Slowlyo\OwlAdmin\Extend\Extension;
use Slowlyo\OwlAdmin\Renderers\Service;
use Slowlyo\OwlAdmin\Renderers\Markdown;
use Slowlyo\OwlAdmin\Renderers\CRUDTable;
use Slowlyo\OwlAdmin\Renderers\AjaxAction;
use Slowlyo\OwlAdmin\Renderers\TextControl;
use Slowlyo\OwlAdmin\Renderers\FileControl;
use Slowlyo\OwlAdmin\Renderers\TableColumn;
use Slowlyo\OwlAdmin\Renderers\DialogAction;
use Slowlyo\OwlAdmin\Renderers\DrawerAction;
use Slowlyo\OwlAdmin\Renderers\SchemaPopOver;
use Slowlyo\OwlAdmin\Controllers\AdminController;

class ExtensionController extends AdminController
{
    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function index()
    {
        if ($this->actionOfGetData()) {
            $data = [];
            foreach (Admin::extension()->all() as $extension) {
                $data[] = $this->each($extension);
            }

            return $this->response()->success(['rows' => $data]);
        }

        $page = $this->basePage()->body($this->list());

        return $this->response()->success($page);
    }

    protected function each($extension)
    {
        $property = $extension->composerProperty;

        $name = $extension->getName();
        $version = $extension->getVersion();

        return [
            'id' => $name,
            'alias' => $extension->getAlias(),
            'logo' => $extension->getLogoBase64(),
            'name' => $name,
            'version' => $version,
            'description' => $property->description,
            'authors' => $property->authors,
            'homepage' => $property->homepage,
            'enabled' => $extension->enabled(),
            'extension' => $extension,
            'doc' => $extension->getDocs(),
            'has_setting' => $extension->settingForm() instanceof Form,
            'used' => $extension->used(),
        ];
    }

    public function list()
    {
        return CRUDTable::make()
            ->perPage(20)
            ->affixHeader(false)
            ->filterTogglable()
            ->filterDefaultVisible(false)
            ->api($this->getListGetDataPath())
            ->perPageAvailable([10, 20, 30, 50, 100, 200])
            ->footerToolbar(['switch-per-page', 'statistics', 'pagination'])
            ->loadDataOnce()
            ->source('${rows | filter:alias:match:keywords}')
            ->filter(
                $this->baseFilter()->body([
                    TextControl::make()
                        ->name('keywords')
                        ->label(__('admin.extensions.form.name'))
                        ->placeholder(__('admin.extensions.filter_placeholder'))
                        ->size('md'),
                ])
            )
            ->headerToolbar([
                amis('reload')->align('left'),
                $this->createExtend(),
                $this->localInstall(),
                $this->moreExtend(),
                amis('filter-toggler')->align('right'),
            ])
            ->columns([
                amis()->TableColumn('alias', __('admin.extensions.form.name'))
                    ->type('tpl')
                    ->tpl('
<div class="flex">
    <div> <img src="${logo}" class="w-10 mr-4"/> </div>
    <div>
        <div><a class="text-gray-900" href="${homepage}" target="_blank">${alias | truncate:30}</a></div>
        <div class="text-gray-400">${name}</div>
    </div>
</div>
'),
                amis()->TableColumn('author', __('admin.extensions.card.author'))
                    ->type('tpl')
                    ->tpl('<div>${authors[0].name}</div> <span class="text-gray-400">${authors[0].email}</span>'),
                $this->rowActions([
                    DrawerAction::make()->label(__('admin.show'))->className('p-0')->level('link')->drawer(
                        Drawer::make()
                            ->size('lg')
                            ->title('README.md')
                            ->actions([])
                            ->closeOnOutside()
                            ->closeOnEsc()
                            ->body(Markdown::make()->name('${doc | raw}')->options(['html' => true, 'breaks' => true]))
                    ),
                    DrawerAction::make()
                        ->label(__('admin.extensions.setting'))
                        ->level('link')
                        ->visibleOn('${has_setting && enabled}')
                        ->drawer(
                            Drawer::make()->title(__('admin.extensions.setting'))->resizable()->closeOnOutside()->body(
                                Service::make()
                                    ->schemaApi([
                                        'url' => admin_url('dev_tools/extensions/config_form'),
                                        'method' => 'post',
                                        'data' => [
                                            'id' => '${id}',
                                        ],
                                    ])
                            )->actions([])
                        ),
                    AjaxAction::make()
                        ->label('${enabled ? "' . __('admin.extensions.disable') . '" : "' . __('admin.extensions.enable') . '"}')
                        ->level('link')
                        ->className(["text-success" => '${!enabled}', "text-danger" => '${enabled}'])
                        ->api([
                            'url' => admin_url('dev_tools/extensions/enable'),
                            'method' => 'post',
                            'data' => [
                                'id' => '${id}',
                                'enabled' => '${enabled}',
                            ],
                        ])
                        ->confirmText('${enabled ? "' . __('admin.extensions.disable_confirm') . '" : "' . __('admin.extensions.enable_confirm') . '"}'),
                    AjaxAction::make()
                        ->label(__('admin.extensions.uninstall'))
                        ->level('link')
                        ->className('text-danger')
                        ->api([
                            'url' => admin_url('dev_tools/extensions/uninstall'),
                            'method' => 'post',
                            'data' => ['id' => '${id}'],
                        ])
                        ->visibleOn('${used}')
                        ->confirmText(__('admin.extensions.uninstall_confirm')),
                ]),
            ]);
    }

    /**
     * 创建扩展
     *
     * @return DialogAction
     */
    public function createExtend()
    {
        return DialogAction::make()
            ->label(__('admin.extensions.create_extension'))
            ->icon('fa fa-add')
            ->level('success')
            ->dialog(
                Dialog::make()->title(__('admin.extensions.create_extension'))->body(
                    Form::make()->mode('normal')->api($this->getStorePath())->body([
                        Alert::make()
                            ->level('info')
                            ->showIcon()
                            ->body(__('admin.extensions.create_tips', ['dir' => config('admin.extension.dir')])),
                        TextControl::make()
                            ->name('name')
                            ->label(__('admin.extensions.form.name'))
                            ->placeholder('eg: slowlyo/owl-admin')
                            ->required(),
                        TextControl::make()
                            ->name('namespace')
                            ->label(__('admin.extensions.form.namespace'))
                            ->placeholder('eg: Slowlyo\Notice')
                            ->required(),
                    ])
                )
            );
    }

    public function store(Request $request)
    {
        $extension = Extension::make();

        $extension->createDir($request->name, $request->namespace);

        if ($extension->hasError()) {
            return $this->response()->fail($extension->getError());
        }

        //创建扩展事件
        ExtensionChanged::dispatch($request->name, 'create');

        return $this->response()->successMessage(
            __('admin.successfully_message', ['attribute' => __('admin.extensions.create')])
        );
    }

    /**
     * 本地安装
     *
     * @return DialogAction
     */
    public function localInstall()
    {
        return DialogAction::make()
            ->label(__('admin.extensions.local_install'))
            ->icon('fa-solid fa-cloud-arrow-up')
            ->dialog(
                Dialog::make()->title(__('admin.extensions.local_install'))->showErrorMsg(false)->body(
                    Form::make()->mode('normal')->api('post:' . admin_url('dev_tools/extensions/install'))->body([
                        FileControl::make()->name('file')->label()->required()->drag()->accept('.zip'),
                    ])
                )
            );
    }

    /**
     * 获取更多扩展
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function more()
    {
        $q = request('q');
        // 加速
        $url = 'http://admin-packagist.dev.slowlyo.top?q=' . $q;

        $result = file_get_contents($url);

        // 如果哪天加速服务挂了，就用官方的
        if (!$result) {
            $url = 'https://packagist.org/search.json?tags=owl-admin&per_page=15&q=' . $q;
            $result = file_get_contents($url);
        }

        return $this->response()->success(json_decode($result, true));
    }

    /**
     * 更多扩展
     *
     * @return DrawerAction
     */
    public function moreExtend()
    {
        return DrawerAction::make()
            ->label(__('admin.extensions.more_extensions'))
            ->icon('fa-regular fa-lightbulb')
            ->drawer(
                Drawer::make()
                    ->title(__('admin.extensions.more_extensions'))
                    ->size('xl')
                    ->closeOnEsc()
                    ->closeOnOutside()
                    ->body(
                        CRUDTable::make()
                            ->perPage(20)
                            ->affixHeader(false)
                            ->filterTogglable()
                            ->loadDataOnce()
                            ->filter(
                                $this->baseFilter()->body([
                                    TextControl::make()
                                        ->name('keywords')
                                        ->label('关键字')
                                        ->placeholder('输入关键字搜索')
                                        ->size('md'),
                                ])
                            )
                            ->filterDefaultVisible(false)
                            ->api('post:' . admin_url('dev_tools/extensions/more') . '?q=${keywords}')
                            ->perPage(15)
                            ->footerToolbar(['statistics', 'pagination'])
                            ->headerToolbar([
                                amis('reload')->align('right'),
                                amis('filter-toggler')->align('right'),
                            ])->columns([
                                TableColumn::make()->name('name')->label('名称')->width(200)
                                    ->type('tpl')
                                    ->tpl('<a href="${url}" target="_blank" title="打开 Packagist">${name}</a>'),
                                TableColumn::make()
                                    ->name('description')
                                    ->label('描述')
                                    ->type('tpl')
                                    ->tpl('${description|truncate: 50}')
                                    ->popOver(
                                        SchemaPopOver::make()->trigger('hover')->body(
                                            Tpl::make()->tpl('${description}')
                                        )->position('left-top')
                                    ),
                                TableColumn::make()->name('repository')->label('仓库')
                                    ->type('tpl')
                                    ->tpl('<a href="${repository}" target="_blank" title="打开代码仓库">${repository|truncate: 50}</a>'),
                                TableColumn::make()->name('downloads')->label('下载量')->width(100),
                                TableColumn::make()
                                    ->name('${"composer require " + name}')
                                    ->label('composer 安装命令')
                                    ->width(300)
                                    ->copyable()
                                    ->type('tpl')
                                    ->tpl('${"composer require " + name}'),
                            ])
                    )
                    ->actions([])
            );
    }

    /**
     * 安装
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function install(Request $request)
    {
        $file = $request->input('file');

        if (!$file) {
            return $this->response()->fail(__('admin.extensions.validation.file'));
        }

        try {
            $path = $this->getFilePath($file);

            $manager = Admin::extension();

            $extensionName = $manager->extract($path, true);

            if (!$extensionName) {
                return $this->response()->fail(__('admin.extensions.validation.invalid_package'));
            }

            //安装扩展事件
            //ExtensionChanged::dispatch($extensionName,'install');

            return $this->response()->successMessage(
                __('admin.successfully_message', ['attribute' => __('admin.extensions.install')])
            );
        } catch (\Throwable $e) {
            return $this->response()->fail($e->getMessage());
        } finally {
            if (!empty($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function getFilePath($file)
    {
        $disk = Admin::config('admin.upload.disk') ?: 'local';

        $root = Admin::config("filesystems.disks.{$disk}.root");

        if (!$root) {
            throw new \Exception(sprintf('Missing \'root\' for disk [%s].', $disk));
        }

        return rtrim($root, '/') . '/' . $file;
    }

    /**
     * 启用/禁用
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function enable(Request $request)
    {
        Admin::extension()->enable($request->id, !$request->enabled);

        //扩展启用禁用事件
        ExtensionChanged::dispatch($request->id, $request->enabled ? 'enable' : 'disable');

        return $this->response()->successMessage(__('admin.action_success'));
    }

    /**
     * 卸载
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function uninstall(Request $request)
    {
        Admin::extension($request->id)->uninstall();

        //扩展卸载事件
        ExtensionChanged::dispatch($request->id, 'uninstall');

        return $this->response()->successMessage(__('admin.action_success'));
    }

    /**
     * 保存扩展设置
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function saveConfig(Request $request)
    {
        $data = collect($request->all())->except(['extension'])->toArray();

        Admin::extension($request->input('extension'))->saveConfig($data);

        return $this->response()->successMessage(__('admin.save_success'));
    }

    /**
     * 获取扩展设置
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getConfig(Request $request)
    {
        $config = Admin::extension($request->input('extension'))->config();

        return $this->response()->success($config);
    }

    /**
     * 获取扩展设置表单
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function configForm(Request $request)
    {
        $form = Admin::extension($request->id)->settingForm();

        return $this->response()->success($form);
    }
}
