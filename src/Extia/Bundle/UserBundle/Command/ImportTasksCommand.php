<?php

namespace Extia\Bundle\UserBundle\Command;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\PersonQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowQuery;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to launch for import internals from a csv file
 */
class ImportTasksCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        $this->setName("extia:tasks:import")
            ->setDescription("Imports tasks from given file")
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
        $consultantsList = array();

        $handle    = fopen($file, 'r');
        $firstLine = fgetcsv($handle);
        while ($data = fgetcsv($handle)) {
            $data = array(
                'lastname'        => trim($data[0]),
                'firstname'       => trim($data[1]),
                'crh_trigram'     => trim($data[2]),
                'last_monitoring' => trim($data[3]),
            );

            $consultantsList[] = $data;
        }
        fclose($handle);

        $cltMap      = array();
        $consultants = ConsultantQuery::create()
            ->orderByLastname()
            ->find()
        ;

        foreach ($consultants as $consultant) {
            $cltMap[$consultant->getSlug()] = $consultant;
        }

        $notFound = 0;
        $found    = 0;

        foreach ($consultantsList as $consultantData) {
            // retrieve clt
            $consultant = ConsultantQuery::create()
                ->filterByFirstname(ucfirst(strtolower($consultantData['firstname'])))
                ->filterByLastname(ucfirst(strtolower($consultantData['lastname'])))
                ->findOne()
            ;

            if (empty($consultant)) {
                $backupConsultants = ConsultantQuery::create()
                    ->filterByFirstname('%'.ucfirst(strtolower($consultantData['firstname'])).'%', \Criteria::LIKE)
                    ->filterByLastname('%'.ucfirst(strtolower($consultantData['lastname'])).'%', \Criteria::LIKE)
                    ->find()
                ;

                if ($backupConsultants->count() !== 1) {
                    $notFound++;
                    continue;
                }
                else {
                    $consultant = $backupConsultants->getFirst();
                }
            }

            $found++;

            // create monitoring on given date
            $reviewDate = $consultantData['last_monitoring'];
            $reviewDate = strpos($reviewDate, '/') === false ?
                null :
                \DateTime::createFromFormat('n/d/Y', $reviewDate)
                    ->add(\DateInterval::createFromDateString('+3 months'))
            ;

            $this->getContainer()->get('extia_user.bridge.crh_monitoring')
                ->createMonitoring($consultant, empty($reviewDate) ? null : $reviewDate->format('U'))
            ;

            $output->writeln(sprintf('%s -> %s',
                $consultant->getSlug(),
                empty($reviewDate) ? '' : $reviewDate->format('d/m/y')
            ));
        }

        // annual meeting triggering
        foreach ($cltMap as $slug => $consultant) {

            $contractBeginDate = $consultant->getContractBeginDate();
            $nextMeetingYear   = (int) date('Y');

            // move to next year only to one month back to now (maybe not done ? waiting for clt return ?)
            $month = (int) $contractBeginDate->format('n');
            if ($month < ((int) date('n')) - 1) {
                $nextMeetingYear++;
            }

            $nextMeetingDate = new \DateTime();
            $nextMeetingDate->setDate(
                $nextMeetingYear,
                $contractBeginDate->format('m'),
                $contractBeginDate->format('d')
            );
            $nextMeetingDate->setTime(
                $contractBeginDate->format('h'),
                $contractBeginDate->format('i'),
                $contractBeginDate->format('s')
            );

            $this->getContainer()->get('extia_user.bridge.annual_review')
                ->createReview($consultant, $nextMeetingDate->format('U'))
            ;

            $output->writeln(sprintf('%s : %s -> %s',
                $consultant->getSlug(),
                $contractBeginDate->format('d/m/y'),
                $nextMeetingDate->format('d/m/y')
            ));
        }

        $reports = array(
            'clts'      => ConsultantQuery::create()->count(),
            'traited'   => $found,
            'not_found' => $notFound,
            'workflows' => WorkflowQuery::create()->count(),
        );

        var_dump($reports);
    }
}
