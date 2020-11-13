<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyJobSettingsAction extends AbstractController {

    const SETTINGS_TWIG_TEMPLATE = 'modules/my-job/settings.html.twig';
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers  $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-job/settings", name="my-job-settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        $this->addJobHolidayPool($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    public function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_holidays_pools      = $this->controllers->getMyJobHolidaysPoolController()->getAllNotDeleted();
        $job_holidays_pool_form  = $this->app->forms->jobHolidaysPoolForm();

        $twig_data = [
            'ajax_render'                       => $ajax_render,
            'all_holidays_pools'                => $all_holidays_pools,
            'job_holidays_pool_form'            => $job_holidays_pool_form->createView(),
            'skip_rewriting_twig_vars_to_js'    => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(static::SETTINGS_TWIG_TEMPLATE, $twig_data);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function addJobHolidayPool(Request $request): void {

        $form = $this->app->forms->jobHolidaysPoolForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }
}