<?php declare(strict_types = 1);
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
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


/**
 * @var CView $this
 */
?>
<script>
	/**
	 * @see init.js add.popup event
	 */
	function addPopupValues(list) {
		if (!isset('object', list)) {
			return false;
		}

		const tmpl = new Template($('#dependency-row-tmpl').html());

		for (var i = 0; i < list.values.length; i++) {
			const value = list.values[i];

			if (document.querySelectorAll(`#dependency-table [data-triggerid="${value.triggerid}"]`).length > 0) {
				continue;
			}

			document
				.querySelector('#dependency-table tr:last-child')
				.insertAdjacentHTML('afterend', tmpl.evaluate({
					triggerid: value.triggerid,
					name: value.name,
					url: ((list.object === 'deptrigger_prototype')
							? 'trigger_prototypes.php?form=update&parent_discoveryid=<?= $data['parent_discoveryid'] ?>&triggerid='
							: 'triggers.php?form=update&triggerid=')
						+ value.triggerid
				}));
		}
	}

	function removeDependency(triggerid) {
		jQuery('#dependency_' + triggerid).remove();
		jQuery('#dependencies_' + triggerid).remove();
	}
</script>
