<?php

declare(strict_types=1);

/**
 * @copyright 2021 Anna Larch <anna.larch@nextcloud.com>
 *
 * @author 2021 Anna Larch <anna.larch@nextcloud.com>
 * @author 2023 Laurent Forthomme
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\CalendarResourceManagement\Command;

use OCA\CalendarResourceManagement\Db\ResourceMapper;
use OCA\CalendarResourceManagement\Db\ResourceModel;
use OCP\DB\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateResource extends Command {
	private const UID = 'uid';
	private const BUILDING_ID = 'building_id';
	private const DISPLAY_NAME = 'display_name';
	private const EMAIL = 'email';
	private const TYPE = 'resource_type';
	private const CONTACT = 'contact-person-user-id';

	/** @var LoggerInterface */
	private $logger;

	/** @var ResourceMapper */
	private $resourceMapper;

	public function __construct(LoggerInterface $logger,
								ResourceMapper $resourceMapper) {
		parent::__construct();
		$this->logger = $logger;
		$this->resourceMapper = $resourceMapper;
	}

	/**
	 * @return void
	 */
	protected function configure() {
		$this->setName('calendar-resource:resource:update');
		$this->setDescription('Update a general resource');
		$this->addArgument(self::UID, InputArgument::REQUIRED);
		$this->addArgument(self::BUILDING_ID, InputArgument::OPTIONAL);
		$this->addArgument(self::DISPLAY_NAME, InputArgument::OPTIONAL);
		$this->addArgument(self::EMAIL, InputArgument::OPTIONAL);
		$this->addArgument(self::TYPE, InputArgument::OPTIONAL);
		$this->addOption(self::CONTACT, null, InputOption::VALUE_OPTIONAL);
	}

	/**
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$resourceModel = new ResourceModel();
		$resourceModel->setUid((string)$input->getArgument(self::UID));
		$buildingId = (int)$input->getArgument(self::BUILDING_ID);
		if ($buildingId)
			$resourceModel->setBuildingId($buildingId);
		if ($displayName = (string)$input->getArgument(self::DISPLAY_NAME))
			$resourceModel->setDisplayName($displayName);
		if ($email = (string)$input->getArgument(self::EMAIL))
			$resourceModel->setEmail($email);
		if ($type = (string)$input->getArgument(self::TYPE))
			$resourceModel->setResourceType($type);
		if ($contact = (string)$input->getOption(self::CONTACT))
			$resourceModel->setContactPersonUserId($contact);

		try {
			$updated = $this->resourceMapper->update($resourceModel);
			$output->writeln('<info>Update a Resource with ID:</info>');
			$output->writeln("<info>" . $updated->getId() . "</info>");
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), ['exception' => $e]);
			$output->writeln('<error>Could not update entry: ' . $e->getMessage() . '</error>');
			return 1;
		}

		return 0;
	}
}
