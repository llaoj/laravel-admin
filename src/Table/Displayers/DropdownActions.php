<?php

namespace Encore\Admin\Table\Displayers;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Admin;
use Encore\Admin\Table\Actions\Delete;
use Encore\Admin\Table\Actions\Edit;
use Encore\Admin\Table\Actions\View;
use Illuminate\Support\Arr;

class DropdownActions extends Actions
{
    protected $view = 'admin::table.actions.dropdown';

    /**
     * @var array
     */
    protected $custom = [];

    /**
     * @var array
     */
    protected $default = [];

    /**
     * @var array
     */
    protected $defaultClass = [Edit::class, View::class, Delete::class];

    /**
     * @var string
     */
    protected $dblclick;

    /**
     * @param RowAction $action
     *
     * @return $this
     */
    public function add(RowAction $action)
    {
        $this->prepareAction($action);

        array_push($this->custom, $action);

        return $this;
    }

    /**
     * Prepend default `edit` `view` `delete` actions.
     */
    protected function prependDefaultActions()
    {
        foreach ($this->defaultClass as $class) {
            /** @var RowAction $action */
            $action = new $class();

            $this->prepareAction($action);

            array_push($this->default, $action);
        }
    }

    /**
     * @param RowAction $action
     */
    protected function prepareAction(RowAction $action)
    {
        $action->setTable($this->table)
            ->setColumn($this->column)
            ->setRow($this->row);
    }

    /**
     * Disable view action.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableView(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, View::class);
        } elseif (!in_array(View::class, $this->defaultClass)) {
            array_push($this->defaultClass, View::class);
        }

        return $this;
    }

    /**
     * Disable delete.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableDelete(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Delete::class);
        } elseif (!in_array(Delete::class, $this->defaultClass)) {
            array_push($this->defaultClass, Delete::class);
        }

        return $this;
    }

    /**
     * Disable edit.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableEdit(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Edit::class);
        } elseif (!in_array(Edit::class, $this->defaultClass)) {
            array_push($this->defaultClass, Edit::class);
        }

        return $this;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function dblclick(string $action)
    {
        $this->dblclick = Arr::get([
            'edit'      => Edit::class,
            'view'      => View::class,
            'delete'    => Delete::class,
        ], $action);

        return $this;
    }

    /**
     * @param null|\Closure $callback
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function display($callback = null)
    {
        if ($callback instanceof \Closure) {
            $callback->call($this, $this);
        }

        if ($this->disableAll) {
            return '';
        }

        $this->prependDefaultActions();

        $dblclick = '';

        foreach (array_merge($this->default, $this->custom) as $action) {

            // activate defalut action dblclick
            if ($this->dblclick && $action instanceof $this->dblclick) {
                $dblclick = $action->getElementClass();
                break;
            }

            if ($action->dblclick) {
                $dblclick = $action->getElementClass();
            }
        }

        $variables = [
            'default'  => $this->default,
            'custom'   => $this->custom,
            'dblclick' => $dblclick,
            'table'    => $this->table->tableID,
        ];

        if (empty($variables['default']) && empty($variables['custom'])) {
            return;
        }

        return Admin::view($this->view, $variables);
    }
}
