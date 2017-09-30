<?php
/**
 * Item Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since  __DEPLOY_VERSION__
 */
namespace Joomla\Component\Workflow\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class Transition extends AdminModel
{

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($data)
	{
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$context    = $this->option . '.' . $this->name;
		$app		= Factory::getApplication();

		if ($pk > 0)
		{
			$isNew = false;
		}

		if ($data['to_state_id'] == $data['from_state_id'])
		{
			$this->setError(\JText::_('You choose the same state from and to'));

			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__workflow_transitions'))
			->where($db->qn('from_state_id') . ' = ' . (int) $data['from_state_id'])
			->where($db->qn('to_state_id') . ' = ' . (int) $data['to_state_id']);

		if (!$isNew)
		{
			$query->andWhere($db->qn('id') . ' <> ' . (int) $data['id']);
		}

		$db->setQuery($query);
		$checkDupliaction = $db->loadResult();

		if (!empty($checkDupliaction))
		{
			$this->setError(\JText::_("COM_WORKFLOW_TRANSITION_DUPLICATE"));

			return false;
		}

		$workflowID = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');

		if (empty($data['workflow_id']))
		{
			$data['workflow_id'] = $workflowID;
		}

		return parent::save($data);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return \JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_workflow.transition',
			'transition',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		return $form;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed  The data for the form.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			'com_workflow.edit.transition.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}