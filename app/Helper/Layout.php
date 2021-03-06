<?php

namespace Kanboard\Helper;

use Kanboard\Core\Base;

/**
 * Layout helpers
 *
 * @package helper
 * @author  Frederic Guillot
 */
class Layout extends Base
{
    /**
     * Render a template without the layout if Ajax request
     *
     * @access public
     * @param  string $template Template name
     * @param  array  $params   Template parameters
     * @return string
     */
    public function app($template, array $params = array())
    {
        if ($this->request->isAjax()) {
            return $this->template->render($template, $params);
        }

        if (! isset($params['no_layout']) && ! isset($params['board_selector'])) {
            $params['board_selector'] = $this->projectUserRole->getActiveProjectsByUser($this->userSession->getId());
        }

        return $this->template->layout($template, $params);
    }

    /**
     * Common layout for user views
     *
     * @access public
     * @param  string $template Template name
     * @param  array  $params   Template parameters
     * @return string
     */
    public function user($template, array $params)
    {
        if (isset($params['user'])) {
            $params['title'] = '#'.$params['user']['id'].' '.($params['user']['name'] ?: $params['user']['username']);
        }

        return $this->subLayout('user/layout', 'user/sidebar', $template, $params);
    }

    /**
     * Common layout for task views
     *
     * @access public
     * @param  string $template Template name
     * @param  array  $params   Template parameters
     * @return string
     */
    public function task($template, array $params)
    {
        $params['title'] = '#'.$params['task']['id'].' '.$params['task']['title'];
        return $this->subLayout('task/layout', 'task/sidebar', $template, $params);
    }

    /**
     * Common layout for project views
     *
     * @access public
     * @param  string $template
     * @param  array  $params
     * @param  string $sidebar
     * @return string
     */
    public function project($template, array $params, $sidebar = 'project/sidebar')
    {
        if (empty($params['title'])) {
            $params['title'] = $params['project']['name'];
        } elseif ($params['project']['name'] !== $params['title']) {
            $params['title'] = $params['project']['name'].' &gt; '.$params['title'];
        }

        return $this->subLayout('project/layout', $sidebar, $template, $params);
    }

    /**
     * Common layout for project user views
     *
     * @access public
     * @param  string $template
     * @param  array  $params
     * @return string
     */
    public function projectUser($template, array $params)
    {
        $params['filter'] = array('user_id' => $params['user_id']);
        return $this->subLayout('project_user/layout', 'project_user/sidebar', $template, $params);
    }

    /**
     * Common layout for config views
     *
     * @access public
     * @param  string $template
     * @param  array  $params
     * @return string
     */
    public function config($template, array $params)
    {
        if (! isset($params['values'])) {
            $params['values'] = $this->config->getAll();
        }

        if (! isset($params['errors'])) {
            $params['errors'] = array();
        }

        return $this->subLayout('config/layout', 'config/sidebar', $template, $params);
    }

    /**
     * Common layout for dashboard views
     *
     * @access public
     * @param  string $template
     * @param  array  $params
     * @return string
     */
    public function dashboard($template, array $params)
    {
        return $this->subLayout('app/layout', 'app/sidebar', $template, $params);
    }

    /**
     * Common layout for analytic views
     *
     * @access public
     * @param  string $template
     * @param  array  $params
     * @return string
     */
    public function analytic($template, array $params)
    {
        return $this->subLayout('analytic/layout', 'analytic/sidebar', $template, $params);
    }

    /**
     * Common method to generate a sublayout
     *
     * @access public
     * @param  string $sublayout
     * @param  string $sidebar
     * @param  string $template
     * @param  array  $params
     * @return string
     */
    public function subLayout($sublayout, $sidebar, $template, array $params = array())
    {
        $content = $this->template->render($template, $params);

        if ($this->request->isAjax()) {
            return $content;
        }

        $params['content_for_sublayout'] = $content;
        $params['sidebar_template'] = $sidebar;

        return $this->app($sublayout, $params);
    }
}
