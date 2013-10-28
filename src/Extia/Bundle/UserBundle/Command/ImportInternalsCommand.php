<?php

namespace Extia\Bundle\UserBundle\Command;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\PersonQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;

use Extia\Bundle\GroupBundle\Model\GroupQuery;

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

        InternalQuery::create()->deleteAll();
        PersonQuery::create()->deleteAll();

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

        // groups
        $groups = GroupQuery::create()
            ->filterByLabel(array('IA - Manager', 'Crh'))
            ->find()
            ->toKeyValue('Label', 'Id')
        ;

        $groupMap = array(
            'COMMERCIAL'          => $groups['IA - Manager'],
            'DIRECTION'           => $groups['IA - Manager'],
            'RESSOURCES HUMAINES' => $groups['Crh']
        );

        // first, insert all internals, without setting any tree values
        foreach ($internalsList as $internalData) {
            $internal = new Internal();
            $internal->setPersonTypeId($typeMap[$internalData['type']]);
            $internal->setGroupId($groupMap[$internalData['type']]);

            $internal->setFirstname(ucfirst(strtolower($internalData['firstname'])));
            $internal->setLastname(ucfirst(strtolower($internalData['lastname'])));
            $internal->setEmail($internalData['email']);
            $internal->setBirthdate(
                \DateTime::createFromFormat('d/m/y', $internalData['birthdate'])
            );
            $internal->setContractBeginDate(
                \DateTime::createFromFormat('d/m/y', $internalData['contract_begin_date'])
            );

            $internal->setTrigram(strtoupper(sprintf('%s%s',
                substr($internalData['firstname'], 0, 1),
                substr($internalData['lastname'], 0, 2)
            )));

            if ((int) substr($internalData['phone'], 1, 2) >= 6) {
                $internal->setMobile($internalData['phone']);
            }
            else {
                $internal->setTelephone($internalData['phone']);
            }

            // make root on empty parent
            if (empty($internalData['parent'])) {
                $internal->makeRoot();
            }
            else {
                $internal->setTreeLeft(0);
                $internal->setTreeRight(0);
                $internal->setTreeLevel(1);
            }

            $internal->save();

            $output->writeln(sprintf('%s %s %s %s',
                $internal->getTrigram(),
                $internal->getFirstname(),
                $internal->getLastName(),
                $internal->getEmail()
            ));
        }

        // then, update tree with parent data
        foreach ($internalsList as $internalData) {
            if (empty($internalData['parent'])) {
                continue;
            }

            $internal = InternalQuery::create()
                ->findOneByEmail($internalData['email'])
            ;
            $parent = InternalQuery::create()
                ->findOneByTrigram($internalData['parent'])
            ;

            if (empty($parent)) {
                $output->writeln(sprintf('WARNING :: %s@%s does not exists',
                    $internal->getTrigram(),
                    $internalData['parent']
                ));

                continue;
            }

            $internal->insertAsLastChildOf($parent);
            $internal->save();

            $output->writeln(sprintf('%s << %s',
                $parent->getTrigram(),
                $internal->getTrigram()
            ));
        }
    }
}
