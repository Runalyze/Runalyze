<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Settings;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Tag;
use Runalyze\Bundle\CoreBundle\Entity\TagRepository;
use Runalyze\Bundle\CoreBundle\Form\Settings\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;

/**
 * @Route("/settings/tags")
 * @Security("has_role('ROLE_USER')")
 */
class TagController extends Controller
{
    /**
     * @return TagRepository
     */
    protected function getTagRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Tag');
    }

    /**
     * @Route("/", name="settings-tag")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Account $account)
    {
        $tags = $this->getTagRepository()->findAllFor($account);
        return $this->render('settings/tag/overview.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tag-edit")
     * @ParamConverter("tag", class="CoreBundle:Tag")
     * @param Request $request
     * @param Tag $tag
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tagEditAction(Request $request, Tag $tag, Account $account)
    {
        if ($tag->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(TagType::class, $tag,[
            'action' => $this->generateUrl('tag-edit', ['id' => $tag->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getTagRepository()->save($tag, $account);
            return $this->redirectToRoute('settings-tag');
        }

        return $this->render('settings/tag/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tag-delete")
     * @ParamConverter("tag", class="CoreBundle:Tag")
     */
    public function tagDeleteAction(Request $request, Tag $tag, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteTag', $request->get('t'))) {
            $this->addFlash('notice', $this->get('translator')->trans('Invalid token.'));
            return $this->redirect($this->generateUrl('settings-tags'));
        }
        if ($tag->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($tag);
        $em->flush();
        $this->addFlash('notice', $this->get('translator')->trans('Tag has been deleted.'));

        return $this->redirect($this->generateUrl('settings-tag'));
    }
}