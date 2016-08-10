<?php
namespace app\models;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model {
    
    public $username;
    public $password;
    public $rememberMe = true;
    public $errors;
    public $message = '';
    public $success = 1;
    private $_user;

    /**
     * @return array the validation rules.
     */
    public function rules() {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            ['username', 'validateUsername'],
        ];
    }

    /**
     * Validates the username.
     * This method serves as the inline validation for username.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUsername($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser($this->username);
            if (!$user) {
                $this->errors = 'Invalid username or email.';
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login() {
        $data = array();
        if ($this->validate() && !$this->errors) {
            $user = $this->getLoggedInUser();
            $data['userInfo'] = $user;
            if (!$user) { // If such a user does not exists with the password
                $this->success = 0;
                $this->errors = 'Incorrect username or password.';
            } else { // if the username and password matches with a record in users table
                $this->message = 'You have logged in successfully';
                /** *****Authenticate the user after successful login ********** */
                $authKey = Users::authenticateUser($user->id);
                $data['userInfo'] = Users::findOne($user->id); 
                $data['userInfo']['authentication_key'] = $authKey['authentication_key'];
                
                /********Fetching the user permissions for a logged in user according to the role ****** */
                $userPermissions = UserType::getUserPermissions($user->type_id);
                if ($userPermissions) {
                    $data['userPermissions'] = $userPermissions;
                }
                if($user->type_id== '7') { //customers
                    $data['customer_id'] = Utils::fetchSingleRecord("id","customers", " user_id ='".$user->id."'");
                }
                if($user->type_id== '8') { //postperson
                    $data['postperson_id'] = Utils::fetchSingleRecord("id","postpeople", " user_id ='".$user->id."'");
                }
                if($user->type_id== '9') { //colleague
                    $data['colleague_id'] = Utils::fetchSingleRecord("id","colleagues", " user_id ='".$user->id."'");
                }
            }
        } else {
            $this->success = 0;
        }

        return array('success' => $this->success, 'errors' => $this->errors, 'message' => $this->message, 'data' => $data);
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser() {
        $this->_user = Users::findByUsername($this->username);
        if(!$this->_user) {
            $this->_user = Users::findByEmail($this->username);
        }

        return $this->_user;
    }

    /**
     * Finds user by [[username] and [password]]
     *
     * @return User|null
     */
    public function getLoggedInUser() {
        $this->_user = Users::findLoggedInUser($this->username, $this->password);
        return $this->_user;
    }

}
