<?php
/**
 * This controller serves the user management pages and tools.
 * @copyright  Copyright (c) 2016 Om Bikash Kumar Das
 * @link  https://github.com/ombikashnitrkl
  */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This controller serves the user management pages and tools.
 * The difference with HR Controller is that operations are technical (CRUD, etc.).
 */
class Users extends CI_Controller {

    /**
     * Default constructor
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->load->model('users_model');
        $this->lang->load('users', $this->language);
    }

    /**
     * Display the list of all users
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function index() {
        $this->auth->checkIfOperationIsAllowed('list_users');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->lang->load('datatable', $this->language);
        $data['users'] = $this->users_model->getUsers();
        $data['title'] = lang('users_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_list_users');
        $data['flash_partial_view'] = $this->load->view('templates/flash', $data, TRUE);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Set a user as active (TRUE) or inactive (FALSE)
     * @param int $id User identifier
     * @param bool $active active (TRUE) or inactive (FALSE)
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function active($id, $active) {
        $this->auth->checkIfOperationIsAllowed('list_users');
        $this->users_model->setActive($id, $active);
        $this->session->set_flashdata('msg', lang('users_edit_flash_msg_success'));
        redirect('users');
    }
    
    /**
     * Enable a user 
     * @param int $id User identifier
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function enable($id) {
        $this->active($id, TRUE);
    }
    
    /**
     * Disable a user 
     * @param int $id User identifier
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function disable($id) {
        $this->active($id, FALSE);
    }

    /**
     * Display the modal pop-up content of the list of employees
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function employees() {
        $this->auth->checkIfOperationIsAllowed('employees_list');
        $data = getUserContext($this);
        $this->lang->load('datatable', $this->language);
        $data['employees'] = $this->users_model->getAllEmployees();
        $data['title'] = lang('employees_index_title');
        $this->load->view('users/employees', $data);
    }

    /**
     * Display details of the connected user (contract, line manager, etc.)
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function myProfile() {
        $this->auth->checkIfOperationIsAllowed('view_myprofile');
        $data = getUserContext($this);
        $data['user'] = $this->users_model->getUsers($this->user_id);
        if (empty($data['user'])) {
            redirect('notfound');
        }
        $data['title'] = lang('users_myprofile_html_title');
        $this->load->model('roles_model');
        $this->load->model('positions_model');
        $this->load->model('contracts_model');
        $this->load->model('organization_model');
        $data['roles'] = $this->roles_model->getRoles();
        $data['manager_label'] = $this->users_model->getName($data['user']['manager']);
        $data['contract_id'] = intval($data['user']['contract']);
        $data['contract_label'] = $this->contracts_model->getName($data['user']['contract']);
        $data['position_label'] = $this->positions_model->getName($data['user']['position']);
        $data['organization_label'] = $this->organization_model->getName($data['user']['organization']);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('users/myprofile', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Display a for that allows updating a given user
     * @param int $id User identifier
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function edit($id) {
        $this->auth->checkIfOperationIsAllowed('edit_user');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('polyglot');
        $data['title'] = lang('users_edit_html_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_create_user');
        
        $this->form_validation->set_rules('firstname', lang('users_edit_field_firstname'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('lastname', lang('users_edit_field_lastname'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('bloodgroup', lang('users_edit_field_bloodgroup'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('login', lang('users_edit_field_login'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('email', lang('users_edit_field_email'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('role[]', lang('users_edit_field_role'), 'required');
        $this->form_validation->set_rules('manager', lang('users_edit_field_manager'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('contract', lang('users_edit_field_contract'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('entity', lang('users_edit_field_entity'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('position', lang('users_edit_field_position'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('datehired', lang('users_edit_field_hired'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('identifier', lang('users_edit_field_identifier'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('previousemployer', lang('users_edit_field_previousemployer'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('language', lang('users_edit_field_language'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('timezone', lang('users_edit_field_timezone'), 'xss_clean|strip_tags');
        if ($this->config->item('ldap_basedn_db')) $this->form_validation->set_rules('ldap_path', lang('users_edit_field_ldap_path'), 'xss_clean|strip_tags');
        
        $data['users_item'] = $this->users_model->getUsers($id);
        if (empty($data['users_item'])) {
            redirect('notfound');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->model('roles_model');
            $this->load->model('positions_model');
            $this->load->model('organization_model');
            $this->load->model('contracts_model');
            $data['contracts'] = $this->contracts_model->getContracts();
            $data['manager_label'] = $this->users_model->getName($data['users_item']['manager']);
            $data['position_label'] = $this->positions_model->getName($data['users_item']['position']);
            $data['organization_label'] = $this->organization_model->getName($data['users_item']['organization']);
            $data['roles'] = $this->roles_model->getRoles();
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->users_model->updateUsers();
            $this->session->set_flashdata('msg', lang('users_edit_flash_msg_success'));
            if (isset($_GET['source'])) {
                redirect($_GET['source']);
            } else {
                redirect('users');
            }
        }
    }

    /**
     * Delete a user. Log it as an error.
     * @param int $id User identifier
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function delete($id) { 
        $this->auth->checkIfOperationIsAllowed('delete_user');
        //Test if user exists
        $data['users_item'] = $this->users_model->getUsers($id);
        if (empty($data['users_item'])) {
            redirect('notfound');
        } else {
            $this->users_model->deleteUser($id);
        }
        log_message('error', 'User #' . $id . ' has been deleted by user #' . $this->session->userdata('id'));
        $this->session->set_flashdata('msg', lang('users_delete_flash_msg_success'));
        redirect('users');
    }

    /**
     * Reset the password of a user
     * Can be accessed by the user itself or by admin
     * @param int $id User identifier
     */
    public function reset($id) {
        $this->auth->checkIfOperationIsAllowed('change_password', $id);

        //Test if user exists
        $data['users_item'] = $this->users_model->getUsers($id);
        if (empty($data['users_item'])) {
            log_message('debug', '{controllers/users/reset} user not found');
            redirect('notfound');
        } else {
            $data = getUserContext($this);
            $data['target_user_id'] = $id;
            $this->load->helper('form');
            $this->load->library('form_validation');
            $this->form_validation->set_rules('CipheredValue', 'Password', 'required');
            if ($this->form_validation->run() === FALSE) {
                $data['public_key'] = file_get_contents('./assets/keys/public.pem', TRUE);
                $this->load->view('users/reset', $data);
            } else {
                $this->users_model->resetPassword($id, $this->input->post('CipheredValue'));
                
                //Send an e-mail to the user so as to inform that its password has been changed
                $user = $this->users_model->getUsers($id);
                $this->load->library('email');
                $this->load->library('polyglot');
                $usr_lang = $this->polyglot->code2language($user['language']);
                //We need to instance an different object as the languages of connected user may differ from the UI lang
                $lang_mail = new CI_Lang();
                $lang_mail->load('email', $usr_lang);

                $this->load->library('parser');
                $data = array(
                    'Title' => $lang_mail->line('email_password_reset_title'),
                    'Firstname' => $user['firstname'],
                    'Lastname' => $user['lastname'],
                    'bloodgroup' => $user['bloodgroup'],
                );
                $message = $this->parser->parse('emails/' . $user['language'] . '/password_reset', $data, TRUE);
                $this->email->set_encoding('quoted-printable');
                
                if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                    $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
                } else {
                    $this->email->from('do.not@reply.me', 'LMS');
                }
                $this->email->to($user['email']);
                if ($this->config->item('subject_prefix') != FALSE) {
                    $subject = $this->config->item('subject_prefix');
                } else {
                   $subject = '[Jorani] ';
                }
                $this->email->subject($subject . $lang_mail->line('email_password_reset_subject'));
                $this->email->message($message);
                $this->email->send();
                
                //Inform back the user by flash message
                $this->session->set_flashdata('msg', lang('users_reset_flash_msg_success'));
                if ($this->is_hr) {
                    redirect('users');
                }
                else {
                    redirect('home');
                }
            }
        }
    }

    /**
     * Display the form / action Create a new user
     */
    public function create() {
        $this->auth->checkIfOperationIsAllowed('create_user');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('polyglot');
        $data['title'] = lang('users_create_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_create_user');

        $this->load->model('roles_model');
        $data['roles'] = $this->roles_model->getRoles();
        $this->load->model('contracts_model');
        $data['contracts'] = $this->contracts_model->getContracts();
        $data['public_key'] = file_get_contents('./assets/keys/public.pem', TRUE);

        $this->form_validation->set_rules('firstname', lang('users_create_field_firstname'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('lastname', lang('users_create_field_lastname'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('bloodgroup', lang('users_create_field_bloodgroup'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('login', lang('users_create_field_login'), 'required|callback_checkLogin|xss_clean|strip_tags');
        $this->form_validation->set_rules('email', lang('users_create_field_email'), 'required|xss_clean|strip_tags');
        if (!$this->config->item('ldap_enabled')) $this->form_validation->set_rules('CipheredValue', lang('users_create_field_password'), 'required');
        $this->form_validation->set_rules('role[]', lang('users_create_field_role'), 'required');
        $this->form_validation->set_rules('manager', lang('users_create_field_manager'), 'required|xss_clean|strip_tags');
        $this->form_validation->set_rules('contract', lang('users_create_field_contract'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('position', lang('users_create_field_position'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('entity', lang('users_create_field_entity'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('datehired', lang('users_create_field_hired'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('identifier', lang('users_create_field_identifier'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('previousemployer', lang('users_create_field_previousemployer'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('language', lang('users_create_field_language'), 'xss_clean|strip_tags');
        $this->form_validation->set_rules('timezone', lang('users_create_field_timezone'), 'xss_clean|strip_tags');
        if ($this->config->item('ldap_basedn_db')) $this->form_validation->set_rules('ldap_path', lang('users_create_field_ldap_path'), 'xss_clean|strip_tags');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/create', $data);
            $this->load->view('templates/footer');
        } else {
            $password = $this->users_model->setUsers();
            
            //Send an e-mail to the user so as to inform that its account has been created
            $this->load->library('email');
            $usr_lang = $this->polyglot->code2language($this->input->post('language'));
            //We need to instance an different object as the languages of connected user may differ from the UI lang
            $lang_mail = new CI_Lang();
            $lang_mail->load('email', $usr_lang);
            
            $this->load->library('parser');
            $data = array(
                'Title' => $lang_mail->line('email_user_create_title'),
                'BaseURL' => base_url(),
                'Firstname' => $this->input->post('firstname'),
                'Lastname' => $this->input->post('lastname'),
                'bloodgroup' => $this->input->post('bloodgroup'),
                'Login' => $this->input->post('login'),
                'Password' => $password
            );
            $message = $this->parser->parse('emails/' . $this->input->post('language') . '/new_user', $data, TRUE);
            $this->email->set_encoding('quoted-printable');

            if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
            } else {
               $this->email->from('do.not@reply.me', 'LMS');
            }
            $this->email->to($this->input->post('email'));
            if ($this->config->item('subject_prefix') != FALSE) {
                $subject = $this->config->item('subject_prefix');
            } else {
               $subject = '[Jorani] ';
            }
            $this->email->subject($subject . $lang_mail->line('email_user_create_subject'));
            $this->email->message($message);
            $this->email->send();
            
            $this->session->set_flashdata('msg', lang('users_create_flash_msg_success'));
            redirect('users');
        }
    }
   
    /**
     * Form validation callback : prevent from login duplication
     * @param string $login Login
     * @return boolean TRUE if the field is valid, FALSE otherwise
     */
    public function checkLogin($login) {
        if (!$this->users_model->isLoginAvailable($login)) {
            $this->form_validation->set_message('checkLogin', lang('users_create_checkLogin'));
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Ajax endpoint : check login duplication
     */
    public function checkLoginByAjax() {
        header("Content-Type: text/plain");
        if ($this->users_model->isLoginAvailable($this->input->post('login'))) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    /**
     * Action: export the list of all users into an Excel file
     */
    public function export() {
        $this->auth->checkIfOperationIsAllowed('export_user');
        $this->load->library('excel');
        $this->load->view('users/export');
    }
    
    /**
     * Import a list of users (XLS, XLSX or CSV) file.
     * It can be used to mass edit the users
     */
    public function import() {
        $this->auth->checkIfOperationIsAllowed('list_users');
        $data = getUserContext($this);

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload()) {
            $error = array('error' => $this->upload->display_errors());
            $this->load->view('upload_form', $error);
            //redirect index ?
        } else {
            $data = array('upload_data' => $this->upload->data());
            $messages = array();
            array_push($messages, 'Try to detect the file format');
            //TODO : suggest template in upload popup
            
            //TODO: can we read from buffer?
            $valid = false;
            $types = array('Excel2007', 'Excel5');
            foreach ($types as $type) {
                $reader = PHPExcel_IOFactory::createReader($type);
                //$objPHPExcel = PHPExcel_IOFactory::load($inputFile);
                //Identify
                //http://stackoverflow.com/questions/9695695/how-to-use-phpexcel-to-read-data-and-insert-into-database
                //Below is can read method
                //http://stackoverflow.com/questions/13626678/phpexcel-how-to-check-whether-a-xls-file-is-valid-or-not
                if ($reader->canRead($file_path)) {
                    array_push($messages, 'File format detected: ' . $type);
                    $valid = true;
                    break;
                }
            }

            if ($valid) {
              // TODO: load file
              // e.g. PHPExcel_IOFactory::load($file_path)
            } else {
                array_push($messages, 'Unable to detect the file format');
            }

            $data['title'] = lang('users_index_title');
            $data['help'] = $this->help->create_help_link('global_link_doc_page_list_users');
            
            $data['messages'] = $messages;
            
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('users/import', $data);
            $this->load->view('templates/footer');
        }
    }

}
