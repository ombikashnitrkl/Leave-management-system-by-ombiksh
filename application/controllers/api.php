<?php
/**
 * This controller is the entry point for the REST API
 * @copyright  Copyright (c) 2016 Om Bikash Kumar Das
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/ombikashnitrkl
  */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * This class implements a REST API served through an OAuth2 server.
 * In order to use it, you need to insert an OAuth2 client into the database, for example :
 * INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ("testclient", "testpass", "http://fake/");
 * where "testclient" and "testpass" are respectively the login and password.
 * Examples are provided into tests/rest folder.
 */
class Api extends CI_Controller {
    
    /**
     * OAuth2 server used by all methods in order to determine if the user is connected
     * @var OAuth2\Server Authentication server 
     */
    protected $server; 
    
    /**
     * Default constructor
     * Initializing of OAuth2 server
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function __construct() {
        parent::__construct();
        require_once(APPPATH . 'third_party/OAuth2/Autoloader.php');
        OAuth2\Autoloader::register();
        $dsn = 'mysql:dbname=' . $this->db->database . ';host=' . $this->db->hostname;
        $username = $this->db->username;
        $password = $this->db->password;
        $storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
        $this->server = new OAuth2\Server($storage);
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
    }

    /**
     * Get a OAuth2 token
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function token() {
        require_once(APPPATH . 'third_party/OAuth2/Server.php');
        $this->server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }
    
    /**
     * Get the list of contracts or a specific contract
     * @param int $id Unique identifier of a contract
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function contracts($id = 0) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('contracts_model');
            $result = $this->contracts_model->getContracts($id);
            echo json_encode($result);
        }
    }
    
    /**
     * Get the list of entitled days for a given contract
     * @param int $id Unique identifier of an contract
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function entitleddayscontract($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('entitleddays_model');
            $result = $this->entitleddays_model->getEntitledDaysForContract($id);
            echo json_encode($result);
        }
    }
    
    /**
     * Add entitled days to a given contract
     * @param int $id Unique identifier of an contract
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function addentitleddayscontract($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('entitleddays_model');
            $startdate = $this->input->post('startdate');
            $enddate = $this->input->post('enddate');
            $days = $this->input->post('days');
            $type = $this->input->post('type');
            $description = $this->input->post('description');
            $result = $this->entitleddays_model->addEntitledDaysToContract($id, $startdate, $enddate, $days, $type, $description);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }
    
    /**
     * Get the list of entitled days for a given employee
     * @param int $id Unique identifier of an employee
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function entitleddaysemployee($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('entitleddays_model');
            $result = $this->entitleddays_model->getEntitledDaysForEmployee($id);
            echo json_encode($result);
        }
    }
    
    /**
     * Add entitled days to a given employee
     * @param int $id Unique identifier of an employee
     * @copyright   (c) 2016 Ombikash
     */
    public function addentitleddaysemployee($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('entitleddays_model');
            $startdate = $this->input->post('startdate');
            $enddate = $this->input->post('enddate');
            $days = $this->input->post('days');
            $type = $this->input->post('type');
            $description = $this->input->post('description');
            $result = $this->entitleddays_model->addEntitledDaysToEmployee($id, $startdate, $enddate, $days, $type, $description);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }
    
    /**
     * Get the leaves counter of a given user
     * @param int $id Unique identifier of a user
     * @param string $refTmp tmp of the Date of reference (or current date if NULL)
     * @copyright  (c) 2016 Ombikash
     */
    public function leavessummary($id, $refTmp = NULL) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('leaves_model');
            if ($refTmp != NULL) {
                $refDate = date("Y-m-d", $refTmp);
            } else {
                $refDate = date("Y-m-d");
            }
            $result = $this->leaves_model->getLeaveBalanceForEmployee($id, FALSE, $refDate);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }
    
    /**
     * Get the leaves counter of a given user
     * @param string $startTmp tmp of the Start Date
     * @param string $endTmp tmp of the End Date
     * @copyright  (c) 2016 Ombikash
     */
    public function leaves($startTmp, $endTmp) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('leaves_model');
            $result = $this->leaves_model->all($startTmp, $endTmp);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }
    
    /**
     * Get the list of leave types (useful to get the labels into a cache)
     * @copyright  (c) 2016 Ombikash
     */
    public function leavetypes() {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('types_model');
            $result = $this->types_model->getTypes();
            echo json_encode($result);
        }
    }
    
    /**
     * Get the list of positions (useful to get the labels into a cache)
     * @copyright (c) 2016 Ombikash
     */
    public function positions() {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('positions_model');
            $result = $this->positions_model->getPositions();
            echo json_encode($result);
        }
    }
    
    /**
     * Get the department details of a given user (label and ID)
     * @param int $id Identifier of an employee (attached to an entity)
     * @copyright (c) 2016 Ombikash
     */
    public function userdepartment($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('organization_model');
            $result = $this->organization_model->getDepartment($id);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }

    /**
     * Get the list of users or a specific user. The password field is removed from the result set
     * @param int $id Unique identifier of a user
     * @copyright (c) 2016 Ombikash
     */
    public function users($id = 0) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('users_model');
            $result = $this->users_model->getUsers($id);
            if ($id === 0) {
                foreach($result as $k1=>$q) {
                  foreach($q as $k2=>$r) {
                    if($k2 == 'password') {
                      unset($result[$k1][$k2]);
                    }
                  }
                }
            } else {
                unset($result['password']);
            }
            echo json_encode($result);
        }
    }

    /**
     * Get the list of extra for a given employee
     * @param int $id Unique identifier of an employee
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function userextras($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('overtime_model');
            $result = $this->overtime_model->getExtrasOfEmployee($id);
            echo json_encode($result);
        }
    }
    
    /**
     * Get the list of leaves for a given employee
     * @param int $id Unique identifier of an employee
     * @copyright  Copyright (c) 2016 Ombikash
     */
    public function userleaves($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('leaves_model');
            $result = $this->leaves_model->getLeavesOfEmployee($id);
            echo json_encode($result);
        }
    }
    
    /**
     * Get the monthly presence stats for a given employee
     * @param int $id Unique identifier of an employee
     * @param int $month Month number [1-12]
     * @param int $year Year number (XXXX)
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.0
     */
    public function monthlypresence($id, $month, $year) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('users_model');
            $employee = $this->users_model->getUsers($id);
            if (!isset($employee['contract'])) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                $this->load->model('leaves_model');
                $this->load->model('dayoffs_model');
                $start = sprintf('%d-%02d-01', $year, $month);
                $lastDay = date("t", strtotime($start));    //last day of selected month
                $end = sprintf('%d-%02d-%02d', $year, $month, $lastDay);
                $result = new stdClass();
                $linear = $this->leaves_model->linear($id, $month, $year, FALSE, FALSE, TRUE, FALSE);
                $result->leaves = $this->leaves_model->monthlyLeavesDuration($linear);
                $result->dayoffs = $this->dayoffs_model->lengthDaysOffBetweenDates($employee['contract'], $start, $end);
                $result->total = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $result->start = $start;
                $result->end = $end;
                $result->open = $result->total - $result->dayoffs;
                $result->work = $result->open - $result->leaves;
                echo json_encode($result);
            }
        }
    }

    /**
     * Delete an employee from the database
     * This is not recommended. Consider moving it into an archive entity of your organization
     * @param int $id Unique identifier of an employee
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.0
     */
    public function deleteuser($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('users_model');
            $employee = $this->users_model->getUsers($id);
            if (count($employee) == 0) {
                $this->output->set_header("HTTP/1.1 404 Not Found");
            } else {
                $this->users_model->deleteUser($id);
                echo json_encode("OK");
            }
        }
    }
    
    /**
     * Update an employee
     * Updated fields are passed by POST parameters
     * @param int $id Unique identifier of an employee
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.0
     */
    public function updateuser($id) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('users_model');
            $data = array();
            if ($this->input->post('firstname')!=FALSE) {$data['firstname']= $this->input->post('firstname');}
            if ($this->input->post('lastname')!=FALSE) {$data['lastname']= $this->input->post('lastname');}
            if ($this->input->post('login')!=FALSE) {$data['login']= $this->input->post('login');}
            if ($this->input->post('email')!=FALSE) {$data['email']= $this->input->post('email');}
            if ($this->input->post('password')!=FALSE) {$data['password']= $this->input->post('password');}
            if ($this->input->post('role')!=FALSE) {$data['role']= $this->input->post('role');}
            if ($this->input->post('manager')!=FALSE) {$data['manager']= $this->input->post('manager');}
            if ($this->input->post('organization')!=FALSE) {$data['organization']= $this->input->post('organization');}
            if ($this->input->post('contract')!=FALSE) {$data['contract']= $this->input->post('contract');}
            if ($this->input->post('position')!=FALSE) {$data['position']= $this->input->post('position');}
            if ($this->input->post('datehired')!=FALSE) {$data['datehired']= $this->input->post('datehired');}
            if ($this->input->post('identifier')!=FALSE) {$data['identifier']= $this->input->post('identifier');}
            if ($this->input->post('language')!=FALSE) {$data['language']= $this->input->post('language');}
            if ($this->input->post('timezone')!=FALSE) {$data['timezone']= $this->input->post('timezone');}
            if ($this->input->post('ldap_path')!=FALSE) {$data['ldap_path']= $this->input->post('ldap_path');}
            if ($this->input->post('country')!=FALSE) {$data['country']= $this->input->post('country');}
            if ($this->input->post('calendar')!=FALSE) {$data['calendar']= $this->input->post('calendar');}
            $result = $this->users_model->updateUserByApi($id, $data);
            if (empty($result)) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            } else {
                echo json_encode($result);
            }
        }
    }
    
    /**
     * Create an employee (fields are passed by POST parameters)
     * Returns the new inserted id
     * @param bool $sendEmail Send an Email to the new employee (FALSE by default)
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.0
     */
    public function createuser($sendEmail = FALSE) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('users_model');
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $login = $this->input->post('login');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $role = $this->input->post('role');
            $manager = $this->input->post('manager');
            $organization = $this->input->post('organization');
            $contract = $this->input->post('contract');
            $position = $this->input->post('position');
            $datehired = $this->input->post('datehired');
            $identifier = $this->input->post('identifier');
            $language = $this->input->post('language');
            $timezone = $this->input->post('timezone');
            $ldap_path = $this->input->post('ldap_path');
            //$active = $this->input->post('active');         //Not used
            $country = $this->input->post('country');   //Not used
            $calendar = $this->input->post('calendar'); //Not used
            
            //Prevent misinterpretation of content
            if ($datehired == FALSE) {$datehired = NULL;}
            if ($organization == FALSE) {$organization = NULL;}
            if ($identifier == FALSE) {$identifier = NULL;}
            if ($timezone == FALSE) {$timezone = NULL;}
            if ($contract == FALSE) {$contract = NULL;}
            if ($position == FALSE) {$position = NULL;}
            if ($manager == FALSE) {$manager = NULL;}
            
            //Set default values
            $this->load->library('polyglot');
            if ($language == FALSE) {$language = $this->polyglot->language2code($this->config->item('language'));}
            
            //Generate a random password if the field is empty
            if ($password == FALSE) {
                $password = $this->users_model->randomPassword(8);
            }
            //Check mandatory fields
            if ($firstname == FALSE || $lastname == FALSE || $login == FALSE || $email == FALSE || $role == FALSE) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
                log_message('error', 'Mandatory fields are missing.');
            } else {
                if ($this->users_model->isLoginAvailable($login)) {
                    $result = $this->users_model->insertUserByApi($firstname, $lastname, $login, $email, $password, $role,
                        $manager, $organization, $contract, $position, $datehired, $identifier, $language, $timezone,
                        $ldap_path, TRUE, $country, $calendar);
                    
                    if($sendEmail == TRUE) {
                        //Send an e-mail to the user so as to inform that its account has been created
                        $this->load->library('email');
                        $usr_lang = $this->polyglot->code2language($language);
                        $this->lang->load('users', $usr_lang);
                        $this->lang->load('email', $usr_lang);

                        $this->load->library('parser');
                        $data = array(
                            'Title' => lang('email_user_create_title'),
                            'BaseURL' => base_url(),
                            'Firstname' => $firstname,
                            'Lastname' => $lastname,
                            'Login' => $login,
                            'Password' => $password
                        );
                        $message = $this->parser->parse('emails/' . $language . '/new_user', $data, TRUE);
                        $this->email->set_encoding('quoted-printable');

                        if ($this->config->item('from_mail') != FALSE && $this->config->item('from_name') != FALSE ) {
                            $this->email->from($this->config->item('from_mail'), $this->config->item('from_name'));
                        } else {
                           $this->email->from('do.not@reply.me', 'LMS');
                        }
                        $this->email->to($email);
                        if ($this->config->item('subject_prefix') != FALSE) {
                            $subject = $this->config->item('subject_prefix');
                        } else {
                           $subject = '[Jorani] ';
                        }
                        $this->email->subject($subject . lang('email_user_create_subject'));
                        $this->email->message($message);
                        $this->email->send();
                    }
                    echo json_encode($result);
                } else {
                    $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
                    log_message('error', 'This login is not available.');
                }
            }
        }
    }

    /**
     * Create a leave request (fields are passed by POST parameters).
     * This function doesn't send e-mails and it is used for imposed leaves
     * Returns the new inserted id.
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.0
     */
    public function createleave() {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('leaves_model');
            $startdate = $this->input->post('startdate');
            $enddate = $this->input->post('enddate');
            $status = $this->input->post('status');
            $employee = $this->input->post('employee');
            $cause = $this->input->post('cause');
            $startdatetype = $this->input->post('startdatetype');
            $enddatetype = $this->input->post('enddatetype');
            $duration = $this->input->post('duration');
            $type = $this->input->post('type');

            //Prevent misinterpretation of content
            if ($cause == FALSE) {$cause = NULL;}
            
            //Check mandatory fields
            if ($startdate == FALSE || $enddate == FALSE || $status === FALSE || $employee === FALSE 
                    || $startdatetype == FALSE || $enddatetype == FALSE || $duration === FALSE || $type === FALSE) {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
                log_message('error', 'Mandatory fields are missing.');
            } else {
                    $result = $this->leaves_model->createLeaveByApi($startdate, $enddate, $status, $employee, $cause,
                                             $startdatetype, $enddatetype, $duration, $type);
                    echo json_encode($result);
            }
        }
    }
    
    /**
     * Get the list of employees attached to an entity
     * @param int $id Identifier of the entity
     * @param bool $children If TRUE, we include sub-entities, FALSE otherwise
     * @copyright  Copyright (c) 2016 Ombikash
     * @since 0.4.3
     */
    public function getListOfEmployeesInEntity($id, $children) {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
        } else {
            $this->load->model('organization_model');
            $children = filter_var($children, FILTER_VALIDATE_BOOLEAN);
            $result = $this->organization_model->allEmployees($id, $children);
            echo json_encode($result);
        }
    }    

}
