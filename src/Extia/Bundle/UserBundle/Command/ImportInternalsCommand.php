<?php

namespace Extia\Bundle\UserBundle\Command;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\PersonQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;
use Extia\Bundle\UserBundle\Model\Agency;

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
    protected $internalsList;

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


    protected function makeParent($internal, $parentTrigram)
    {
        $parent = InternalQuery::create()
            ->findOneByTrigram($parentTrigram)
        ;

        if (empty($parent)) {
            return;
        }

        if ($parent->getTreeLevel() == 999) { // has any parent yet
            $this->makeParent($parent, $this->internalsList[$parentTrigram]['parent']);
        }

        $internal->insertAsLastChildOf($parent);
        $internal->save();
        $parent->save();
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

        // "hard" trigram
        $hardTrigrams = array(
            'edegand@extia.fr'    => 'EDE',
            'edeschamps@extia.fr' => 'EMD',
            'adupessey@extia.fr'  => 'AND',
            'adupuy@extia.fr'     => 'ADU',
            'mdefromont@extia.fr' => 'MDF',
            'asadellah@extia.fr'  => 'AIS'
        );

        // for multiple rows
        $internalsList = array();

        $handle    = fopen($file, 'r');
        $firstLine = fgetcsv($handle);
        while ($data = fgetcsv($handle)) {
            $data = array(
                'lastname'            => trim($data[0]),
                'firstname'           => trim($data[1]),
                'fullname'            => trim($data[2]),
                'birthdate'           => trim($data[3]),
                'email'               => trim($data[4]),
                'phone'               => trim($data[5]),
                'contract_begin_date' => trim($data[6]),
                'parent'              => trim($data[7]),
                'type'                => trim($data[8]),
            );

            if (!in_array($data['type'], array('COMMERCIAL', 'DIRECTION', 'RESSOURCES HUMAINES'))) {
                continue;
            }

            $trigram = isset($hardTrigrams[$data['email']]) ?
                $hardTrigrams[$data['email']] :
                strtoupper(sprintf('%s%s',
                    substr($data['firstname'], 0, 1),
                    substr($data['lastname'], 0, 2)
                ))
            ;

            $data['trigram'] = $trigram;

            $internalsList[$data['trigram']] = $data;
        }
        fclose($handle);

        // all person type
        $personTypes = PersonTypeQuery::create()
            ->filterByCode(array('pdg', 'ia', 'crh', 'dir'))
            ->find()
            ->toKeyValue('Code', 'Id')
        ;

        $typeMap = array(
            'COMMERCIAL'          => $personTypes['ia'],
            'DIRECTION'           => $personTypes['pdg'],
            'RESSOURCES HUMAINES' => $personTypes['crh'],
            'DIRECTEUR AGENCE'    => $personTypes['dir'],
        );

        // groups
        $groups = GroupQuery::create()
            ->filterByLabel(array('IA - Manager', 'Crh', 'Directeur général', 'Directeur d\'agence'))
            ->find()
            ->toKeyValue('Label', 'Id')
        ;

        $groupMap = array(
            'COMMERCIAL'          => $groups['IA - Manager'],
            'DIRECTION'           => $groups['Directeur général'],
            'RESSOURCES HUMAINES' => $groups['Crh'],
            'DIRECTEUR AGENCE'    => $groups['Directeur d\'agence'],
        );

        $this->internalsList = $internalsList;

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

            $internal->setTrigram($internalData['trigram']);

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
                $internal->setTreeLevel(999);
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
                ->filterByTreeLevel(999)
                ->findOneByEmail($internalData['email'])
            ;

            if (empty($internal)) {
                continue;
            }

            $this->makeParent($internal, $internalData['parent']);
        }


        // create agencies

        $agencyList = array(
            'agence_bch'      =>  array('Benjamin', 'Charle'),
            'agence_tan'      =>  array('Thibault', 'Anssens'),
            'agence_nrj'      =>  array('Benjamin', 'Reynier'),
            'agence_rha'      =>  array('Romain', 'Vacher'),
            'agence_paca'     =>  array('Guillaume', 'Zanetti'),
            'agence_belgique' =>  array('Fabrice', 'Claudon'),
        );

        foreach ($agencyList as $name => $dir) {
            $agency = new Agency();
            $agency->setCode($name);
            $agency->save();

            $director = InternalQuery::create()
                ->filterByFirstname($dir[0])
                ->filterByLastname($dir[1])
                ->findOne()
            ;

            $director->setAgency($agency);
            $director->setPersonTypeId($typeMap['DIRECTEUR AGENCE']);
            $director->setGroupId($typeMap['DIRECTEUR AGENCE']);
            $director->save();

            InternalQuery::create()
                ->descendantsOf($director)
                ->update(array(
                    'AgencyId' => $agency->getId()
                ))
            ;
        }
    }
}
