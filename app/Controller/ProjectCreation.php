<?php

namespace Kanboard\Controller;

/**
 * Project Creation Controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class ProjectCreation extends Base
{
    /**
     * Display a form to create a new project
     *
     * @access public
     */
    public function create(array $values = array(), array $errors = array())
    {
        $is_private = isset($values['is_private']) && $values['is_private'] == 1;
        $projects_list = array(0 => t('Do not duplicate anything')) + $this->projectUserRole->getActiveProjectsByUser($this->userSession->getId());

        $this->response->html($this->helper->layout->app('project_creation/create', array(
            'values' => $values,
            'errors' => $errors,
            'is_private' => $is_private,
            'projects_list' => $projects_list,
            'title' => $is_private ? t('New private project') : t('New project'),
        )));
    }

    /**
     * Display a form to create a private project
     *
     * @access public
     */
    public function createPrivate(array $values = array(), array $errors = array())
    {
        $values['is_private'] = 1;
        $this->create($values, $errors);
    }

    /**
     * Validate and save a new project
     *
     * @access public
     */
    public function save()
    {
        $values = $this->request->getValues();
        list($valid, $errors) = $this->projectValidator->validateCreation($values);

        if ($valid) {
            $project_id = $this->createOrDuplicate($values);

            if ($project_id > 0) {
                $this->flash->success(t('Your project have been created successfully.'));
                return $this->response->redirect($this->helper->url->to('project', 'show', array('project_id' => $project_id)));
            }

            $this->flash->failure(t('Unable to create your project.'));
        }

        $this->create($values, $errors);
    }

    /**
     * Create or duplicate a project
     *
     * @access private
     * @param  array  $values
     * @return boolean|integer
     */
    private function createOrDuplicate(array $values)
    {
        if ($values['src_project_id'] == 0) {
            return $this->createNewProject($values);
        }

        return $this->duplicateNewProject($values);
    }

    /**
     * Save a new project
     *
     * @access private
     * @param  array  $values
     * @return boolean|integer
     */
    private function createNewProject(array $values)
    {
        $project = array(
            'name' => $values['name'],
            'is_private' => $values['is_private'],
        );

        return $this->project->create($project, $this->userSession->getId(), true);
    }

    /**
     * Creatte from another project
     *
     * @access private
     * @param  array  $values
     * @return boolean|integer
     */
    private function duplicateNewProject(array $values)
    {
        $selection = array();

        foreach ($this->projectDuplication->getOptionalSelection() as $item) {
            if (isset($values[$item]) && $values[$item] == 1) {
                $selection[] = $item;
            }
        }

        return $this->projectDuplication->duplicate(
            $values['src_project_id'],
            $selection,
            $this->userSession->getId(),
            $values['name'],
            $values['is_private'] == 1
        );
    }
}
