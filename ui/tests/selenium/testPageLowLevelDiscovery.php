<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/CWebTest.php';

class testPageLowLevelDiscovery extends CWebTest {


	private $type_selection = ['Zabbix agent', 'Zabbix agent (active)', 'Simple check', 'SNMP agent', 'Zabbix internal', 'Zabbix trapper', 'External check',
								'Database monitor', 'HTTP agent', 'IPMI agent', 'SSH agent', 'TELNET agent', 'JMX agent', 'Dependent item', 'all'];
	private $state_selection = ['Normal', 'Not supported', 'all'];
	private $status_selection = ['all', 'Enabled', 'Disabled'];

	public function testPageLowLevelDiscovery_CheckFilterForms() {
		$this->page->login()->open('host_discovery.php?filter_set=1&filter_hostids%5B0%5D=90001');
		$form = $this->query('name:zbx_filter')->one()->asForm();

		// Check all field names.
		$fields_names = ['Host groups', 'Hosts', 'Name', 'Key', 'Type', 'Update interval', 'Keep lost resources period', 'SNMP OID', 'State', 'Status'];
		$labels = $form->getLabels()->asText();
		$this->assertEquals($fields_names, $labels);

		// Check all dropdowns.
		$all_dropdown = ['Type', 'State', 'Status'];
		foreach ($all_dropdown as $dropdown) {
			$true_dropdown = $form->query('name:filter_'.lcfirst($dropdown))->asDropdown();
			switch ($dropdown) {
				case 'Type':
					foreach ($this -> type_selection as $type) {
						$true_dropdown->one()->select($type);
						$form->submit();
						$this->assertEquals($true_dropdown->one()->getValue(), $type);
					}
					break;
				case 'State':
					foreach ($this->state_selection as $state) {
						$true_dropdown->one()->select($state);
						$form -> submit();
						$this ->assertEquals($true_dropdown->one()->getValue(), $state);
					}
					break;
				case 'Status':
					foreach ($this->status_selection as $status) {
						$true_dropdown->one()->select($status);
						$form->submit();
						$this->assertEquals($true_dropdown->one()->getValue(), $status);
					}
					break;
			}
		}

		// Check that all buttons exists.
		$buttons_name = ['Apply', 'Reset'];
		foreach ($buttons_name as $button){
			$this->assertTrue($form->query('button:'.$button)->one()->isPresent());
		}

		// Check all headers that exists. Especially host.
		$headers_name = ['Host', 'Name', 'Items', 'Triggers', 'Graphs', 'Hosts', 'Key', 'Interval', 'Type', 'Status', 'Info'];
		foreach ($headers_name as $header) {
			$this->assertTrue($this->query('xpath://tr//*[contains(text(),"'.$header.'")]')->one()->isPresent());
		}
	}

	public static function getForResetButtonCheck() {
		return [
			[
				[
					'fields' => [
						'Name' => 'Discovery rule 3',
						'Key' => 'key3',
						'Type' => 'Zabbix agent',
						'Update interval' => '30s',
						'Status' => 'Enabled'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getForResetButtonCheck
	 */
	public function testPageLowLevelDiscovery_ResetButton($data) {
		$this->page->login()->open('host_discovery.php?filter_set=1&filter_hostids%5B0%5D=90001');
		$form = $this->query('name:zbx_filter')->one()->asForm();

		// Filling fields with neede discovery rule info.
		$form->fill($data['fields']);
		$form->submit();

		// Checking that needed discovery displayed and he is only one.
		$table = $this->query('class:list-table')->asTable()->one();
		$row = $table->findRow('Name', 'Discovery rule 3');
		$this->assertTrue($row->isPresent());
		$row_count = $table->getRows()->count();
		$this->assertEquals(1, $row_count);

		// After pressing reset button, check that there is 3 discovery rules displayed again.
		$this->query('button:Reset')->one()->click();
		$row_count = $table->getRows()->count();
		$this->assertEquals(3, $row_count);
	}

	public function testPageLowLevelDiscovery_HostCheck() {

		// Check that Hosts field and host names displayed are similar.
		$this->page->login()->open('host_discovery.php?filter_set=1&filter_hostids%5B0%5D=90001');
		$form = $this->query('name:zbx_filter')->one()->asForm();
		$hosts = $form->getField('Hosts')->asMultiselect();
		$hosts_name = 'Host for host prototype tests';
		$this->assertEquals([$hosts_name], $hosts->getSelected());
		$table = $this->query('class:list-table')->asTable()->one();
		$header_names = ['Discovery rule 1', 'Discovery rule 2', 'Discovery rule 3'];
		foreach ($header_names as $name) {
			$row = $table -> findRow('Name', $name);
			$this->assertEquals($row->getColumnData('Host', $hosts_name), $hosts_name);
		}
	}
}
