<?php
namespace Extia\Bundle\UserBundle\Form\Handler;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\Person;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;


/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 01/08/13
 * Time: 14:13
 */
class PersonHandler
{
    /**
     * @var \Symfony\Component\Form\Form
     */
    private $form;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @param Form    $form
     * @param Request $request
     */
    public function __construct(Form $form, Request $request)
    {
        $this->request = $request;
        $this->form = $form;
    }

    public function process()
    {
        if ('POST' == $this->request->getMethod())
        {
            $this->form->bindRequest($this->request);
            if ($this->form->isValid()) {
                $person = $this->form->getData();



                $this->onSuccess($person, $person['group']);

                return true;
            }
        }

        return false;
    }

    /**
     * Send mail on success
     *
     * @param array $person
     * @param       $group
     */
    protected function onSuccess($person, $group)
    {
        $user = null;

        switch ($group) {
            case 1:
            case 2:
                if (!empty($person['id'])) {

                }
                $user = new Person();
                break;
            case 3:
                $user = new Consultant();
                break;
            default:
                throw new \Exception('Invalid group');
                break;
        }

        $user->setFirstname($person['firstname']);
        $user->setLastname($person['lastname']);
        $user->setEmail($person['email']);
        $user->setPhone($person['phone']);
        $user->setMobile($person['mobile']);
        $user->setPassword($person['password']);

        $user->save();
    }

}
