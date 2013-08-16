<?php

namespace Extia\Bundle\CommentBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * form handler for comment
 * @see Extia/Bundles/CommentBundle/Resources/config/forms.xml
 */
class CommentHandler
{
    public $error;

    protected $securityContext;

    /**
     * construct
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * handle given comment form
     * @param Form    $form
     * @param Request $request
     */
    public function handle(Form $form, Request $request)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('User has to be authenticated to create a comment.');
        }

        $form->submit($request);
        if (!$form->isValid()) {
            return false;
        }

        $comment = $form->getData();

        // injects current user
        $comment->setPerson($this->securityContext->getToken()->getUser());

        return $comment->save();
    }
}
