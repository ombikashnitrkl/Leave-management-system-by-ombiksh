<?php
/**
 * This controller serves the administration pages
 * @copyright  Copyright (c) 2016 Om Bikash Kumar Das
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/ombikashnitrkl
  */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This class serves the administration pages (readonly settings page for the moment).
 * In Jorani the settings are set into a configuration file and not into DB.
 */
class Admin extends CI_Controller {
    
    /**
     * Default constructor
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->lang->load('global', $this->language);
        $this->lang->load('admin', $this->language);
    }
    
    /**
     * Display the settings of the system (extract of config.php)
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function settings() {
        $this->auth->checkIfOperationIsAllowed('list_settings');
        $data = getUserContext($this);
        $data['title'] = 'application/config/config.php';
        $data['help'] = $this->help->create_help_link('global_link_doc_page_settings');
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('admin/settings', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Display the diagnostic of the content (duplicated requests, etc.) and configuration
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function diagnostic() {
        $this->auth->checkIfOperationIsAllowed('list_settings');
        $data = getUserContext($this);
        $data['title'] =lang('admin_diagnostic_title');
        $data['help'] = '';
        $this->load->model('leaves_model');
        $this->load->model('entitleddays_model');
        $this->load->model('dayoffs_model');
        $data['duplicatedLeaves'] = $this->leaves_model->detectDuplicatedRequests();
        $data['wrongDateType'] = $this->leaves_model->detectWrongDateTypes();
        $data['entitlmentOverflow'] = $this->entitleddays_model->detectOverflow();
        $data['daysOffYears'] = $this->dayoffs_model->checkIfDefined(date("Y"));
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('admin/diagnostic', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Output a QRCode containing the URL of the Jorani instance and the e-mail of the connected user
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function qrCode() {
        require_once(APPPATH . 'third_party/QRCode.php');
        $this->load->model('users_model');
        $user = $this->users_model->getUsers($this->user_id);
        $qr = new QRCode();
        $qr = QRCode::getMinimumQRCode(base_url() . '#' . $user['login'] .
                 '#' . $user['email'], QR_ERROR_CORRECT_LEVEL_L);
        echo $qr->printHTML();
    }
}
