<?php

namespace Extia\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to launch for update missions orders function of time
 */
class SynchronizeMissionsCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        $this->setName("extia:missions-sync")
            ->setDescription("Synchronize mission orders to current date");
    }

    /**
     * @see ContainerAwareCommand::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('>>> <comment>Synchronize Rhea mission orders</comment>');

        $report = $this->getContainer()->get('extia_user.domain.mission_order')->synchronize(new \DateTime());

        $output->writeln(sprintf('> <info>mission orders activated</info>    %s', $report['activated_mission_orders'] ));
        $output->writeln(sprintf('> <info>mission monitoring opened</info>   %s', $report['opened_mission_monitoring'] ));

        $output->writeln(sprintf('> <info>mission orders disactivated</info> %s', $report['disactivated_mission_orders'] ));
        $output->writeln(sprintf('> <info>mission monitoring closed</info>   %s', $report['closed_mission_monitoring'] ));
    }
}
