<?php

namespace Auth\Adapter;

use Auth\Entity\Role;
use Auth\Entity\User;
use Auth\Model\Authorization;
use Zend\Authentication\Adapter\DbTable as ZendDbTable;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Db\Sql\Expression as SqlExpr;
use Zend\Db\Sql\Predicate\Operator as SqlOp;

/**
 * Description of DbTable
 *
 * @author seyfer
 */
class DbTable extends ZendDbTable
{

    /**
     * Добавить выборку роли
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        // build credential expression
        if (empty($this->credentialTreatment) || (strpos($this->credentialTreatment, '?') === false)) {
            $this->credentialTreatment = '?';
        }

        $credentialExpression = new SqlExpr(
            '(CASE WHEN ?' . ' = ' . $this->credentialTreatment . ' THEN 1 ELSE 0 END) AS ?', [
            $this->credentialColumn, $this->credential, 'zend_auth_credential_match',
        ], [
                SqlExpr::TYPE_IDENTIFIER, SqlExpr::TYPE_VALUE, SqlExpr::TYPE_IDENTIFIER,
            ]
        );

        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->tableName)
            ->columns(['*', $credentialExpression])
            ->join(['r' => "roles"], "r.id = " . $this->tableName . ".role_id", ['role_id' => 'id',
                'role_name' => 'name'], \Zend\Db\Sql\Select::JOIN_LEFT)
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        return $dbSelect;
    }

    /**
     * Тут сохранить  рез-т в контейнер, если 1
     *
     * @param array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        $this->fillSessionUser($resultIdentity);

        return parent::authenticateValidateResult($resultIdentity);
    }

    /**
     * fill user entity and save to session
     *
     * @param array $result
     */
    protected function fillSessionUser($result)
    {
        $user = new User();
        $user->exchangeArray($result);

        $role = new Role();
        $role->setId($result['role_id']);
        $role->setName($result['role_name']);

        $user->setRole($role);

        $auth = new Authorization();
        $auth->setSessionUser($user);
    }

}
