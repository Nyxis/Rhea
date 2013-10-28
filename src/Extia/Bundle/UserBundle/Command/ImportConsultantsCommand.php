<?php

namespace Extia\Bundle\UserBundle\Command;

use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
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
 * Command to launch for import users from a csv file
 */
class ImportConsultantsCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        $this->setName("extia:consultants:import")
            ->setDescription("Imports mission from given file")
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

        // ConsultantQuery::create()->deleteAll();
        // InternalQuery::create()->deleteAll();
        // PersonQuery::create()->deleteAll();

        // for multiple rows
        $consultantList = array();

        $handle    = fopen($file, 'r');
        $firstLine = fgetcsv($handle);
        while ($data = fgetcsv($handle)) {
            $indexedData = array(
                'period'              => $data[0],
                'lastname'            => $data[1],
                'firstname'           => $data[2],
                'fullname'            => $data[3],
                'birthdate'           => $data[4],
                'email'               => $data[5],
                'mobile'              => $data[6],
                'contract_begin_date' => $data[7],
                'adp'                 => $data[8],
                'crh_lastname'        => $data[9],
                'crh_firstname'       => $data[10],
                'contract_type'       => $data[11],
                'mission_id'          => $data[12],
                'client_name'         => $data[13],
                'manager_trigram'     => $data[14],
                'manager_lastname'    => $data[15],
                'manager_firstname'   => $data[16],
                'debut_mission'       => $data[17],
                'fin_mission'         => $data[18]
            );

            if (empty($indexedData['email']) || $indexedData['email'] == '#N/A') {
                continue;
            }

            // already exists, only adds mission
            $email = $indexedData['email'];
            if (!isset($consultantList[$email])) {
                $consultantList[$email] = array_intersect_key(
                    $indexedData,
                    array_flip(array('lastname', 'firstname', 'fullname', 'birthdate', 'email', 'mobile', 'contract_begin_date', 'crh_lastname', 'crh_firstname', 'contract_type'))
                );

                $consultantList[$email]['missions'] = array();
            }

            $consultantList[$indexedData['email']]['missions'][] = array_intersect_key(
                $indexedData,
                array_flip(array('client_name', 'manager_trigram', 'manager_lastname', 'manager_firstname', 'debut_mission', 'fin_mission'))
            );
        }
        fclose($handle);


        // consultant person type
        $personType = PersonTypeQuery::create()
            ->filterByCode(array('clt'))
            ->findOne()
        ;

        // consultant group
        $group = GroupQuery::create()
            ->filterByLabel(array('Consultant'))
            ->findOne()
        ;

        // tmp manager (calculated with missions)
        $tmpManager = InternalQuery::create()
            ->usePersonTypeQuery()
                ->filterByCode('ia')
            ->endUse()
            ->findOne()
        ;

        // first : create consultant
        foreach ($consultantList as $consultantData) {
            $consultant = new Consultant();
            $consultant->setPersonType($personType);
            $consultant->setGroup($group);

            $consultant->setFirstname(ucfirst(strtolower($consultantData['firstname'])));
            $consultant->setLastname(ucfirst(strtolower($consultantData['lastname'])));
            $consultant->setEmail($consultantData['email']);
            $consultant->setMobile($consultantData['mobile']);

            $consultant->setBirthdate(
                \DateTime::createFromFormat('d/m/y', $consultantData['birthdate'])
            );
            $consultant->setContractBeginDate(
                \DateTime::createFromFormat('d/m/y', $consultantData['contract_begin_date'])
            );

            // find crh
            $consultant->setCrh(InternalQuery::create()
                ->filterByFirstname($consultantData['crh_firstname'])
                ->filterByLastname($consultantData['crh_lastname'])
                ->findOne()
            );

            // tmp manager
            $consultant->setManager($tmpManager);

            $consultant->save();

            $output->writeln(sprintf('%s %s %s',
                $consultant->getFirstname(),
                $consultant->getLastName(),
                $consultant->getEmail()
            ));
        }

        // create missions and orders
        foreach ($consultantList as $consultantData) {
            $consultant = ConsultantQuery::create()
                ->findOneByEmail($consultantData['email'])
            ;

            // missions and orders
            foreach ($consultantData['missions'] as $mission) {
                var_dump($mission);
            }
        }



    }
}



