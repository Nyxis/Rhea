<?php

namespace Extia\Bundle\UserBundle\Command;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to launch for import internals from a csv file
 */
class ImportInternalsCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        $this->setName("extia:internals:import")
            ->setDescription("Imports internals from given file")
            ->addArgument('csv', InputArgument::REQUIRED, 'Consultant data you want to import')
        ;
    }

    /**
     * @see ContainerAwareCommand::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = sprintf('%s/../%s',
            $this->getContainer()->getParameter('kernel.root_dir'),
            $input->getArgument('csv')
        );

        $filesystem = $this->getContainer()->get('filesystem');
        if (!$filesystem->exists($file)) {
            throw new \InvalidArgumentException(sprintf(
                'Given csv file is unreachable, "%s" given.',
                $file
            ));
        }

        // for multiple rows
        $internalsList = array();

        $handle    = fopen($file, 'r');
        $firstLine = fgetcsv($handle);
        while ($data = fgetcsv($handle)) {
            $data = array(
                'lastname'            => $data[0],
                'firstname'           => $data[1],
                'fullname'            => $data[2],
                'birthdate'           => $data[3],
                'email'               => $data[4],
                'phone'               => $data[5],
                'contract_begin_date' => $data[6],
                'parent'              => $data[7],
                'type'                => $data[8],
            );

            if (!in_array($data['type'], array('COMMERCIAL', 'DIRECTION', 'RESSOURCES HUMAINES'))) {
                continue;
            }

            $internalsList[] = $data;
        }
        fclose($handle);

        // all person type
        $personTypes = PersonTypeQuery::create()
            ->filterByCode(array('pdg', 'ia', 'crh'))
            ->find()
            ->toKeyValue('Code', 'Id')
        ;

        $typeMap = array(
            'COMMERCIAL'          => $personTypes['ia'],
            'DIRECTION'           => $personTypes['pdg'],
            'RESSOURCES HUMAINES' => $personTypes['crh']
        );


        // first, insert all internals, without setting any tree values
        foreach ($internalsList as $internalData) {
            $internal = new Internal();
            $internal
        }



        // then, update tree with parent data



        foreach ($consultantList as $consultant) {
            $output->writeln($consultant['email']);
        }
    }
}
