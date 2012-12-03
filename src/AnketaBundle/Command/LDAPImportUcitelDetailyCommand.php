<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import Teacher's given name and family name from LDAP
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */
class LDAPImportUcitelDetailyCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('anketa:ldap:ucitel-detaily')
             ->setDescription('Importuj detaily ucitelov z LDAPu');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract class is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        /* @var $conn \Doctrine\DBAL\Connection */
        $conn = $this->getContainer()->get('database_connection');
        
        /* @var $ldap \AnketaBundle\Integration\LDAPRetriever */
        $ldap = $this->getContainer()->get('anketa.ldap_retriever');
        $ldap->loginIfNotAlready();
        
        $withoutName = $conn->executeQuery(
            "SELECT t.login, t.givenName, t.familyName, t.displayName
             FROM User t 
             WHERE (t.givenName = '' OR t.familyName = ''
                    OR t.displayName IS NULL OR t.displayName = '')
             AND t.login IS NOT NULL AND t.login != ''");

        $conn->beginTransaction();
        
        try {
            $updateTeacher = $conn->prepare(
                    'UPDATE User 
                        SET givenName=:givenName, familyName=:familyName,
                        displayName=:displayName
                        WHERE login=:login LIMIT 1');
            
            while (($row = $withoutName->fetch()) !== false) {
                $login = $row['login'];
                $givenName = $row['givenName'];
                $familyName = $row['familyName'];
                $displayName = $row['displayName'];
                
                if ($input->getOption('verbose')) {
                    $output->writeln($login);
                }
                
                $usernameFilter = '(uid=' . $ldap->escape($login) . ')';
                $ldapInfo = $ldap->searchOne($usernameFilter,
                        array('givenNameU8', 'snU8', 'displayName')); //U8 == UTF-8, sn == surname
                
                if ($ldapInfo === null) {
                    $output->writeln('<info>Pouzivatel ' . $login . ' nebol '.
                        'najdeny v LDAPe</info>');
                    continue;
                }
                
                if (isset($ldapInfo['givenNameU8'][0])) {
                    $ldapGivenName = $ldapInfo['givenNameU8'][0];
                    if (empty($givenName)) {
                        $givenName = $ldapGivenName;
                    }
                    else if ($givenName != $ldapGivenName) {
                        $output->writeln('<info>Pouzivatel ' . $login . ' ma ine meno v ldape (' .
                                $ldapGivenName . ') ako v databaze (' .
                                $givenName . ')</info>');
                    }
                }
                else {
                    $output->writeln('<info>Pouzivatel ' . $login . ' nema meno v ldape</info>');
                }
                
                if (isset($ldapInfo['snU8'][0])) {
                    $ldapFamilyName = $ldapInfo['snU8'][0];
                    if (empty($familyName)) {
                        $familyName = $ldapFamilyName;
                    }
                    else if ($familyName != $ldapFamilyName) {
                        $output->writeln('<info>Pouzivatel ' . $login . ' ma ine priezvisko v ldape (' .
                                $ldapFamilyName . ') ako v databaze (' .
                                $familyName . ')</info>');
                    }
                }
                else {
                    $output->writeln('<info>Pouzivatel ' . $login . ' nema priezvisko v ldape</info>');
                }
                
                if (isset($ldapInfo['displayName'][0])) {
                    $ldapDisplayName = $ldapInfo['displayName'][0];
                    if (empty($displayName)) {
                        $displayName = $ldapDisplayName;
                    }
                    else if ($displayName != $ldapDisplayName) {
                        $output->writeln('<info>Pouzivatel ' . $login . ' ma ine display name v ldape (' .
                                $ldapDisplayName . ') ako v databaze (' .
                                $displayName . ')</info>');
                    }
                }
                else {
                    $output->writeln('<info>Pouzivatel ' . $login . ' nema display name v ldape</info>');
                }
                
                $updateTeacher->bindValue('givenName', $givenName);
                $updateTeacher->bindValue('familyName', $familyName);
                $updateTeacher->bindValue('displayName', $displayName);
                $updateTeacher->bindValue('login', $login);
                $updateTeacher->execute();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $ldap->logoutIfNotAlready();
            throw $e;
        }

        $conn->commit();
        $ldap->logoutIfNotAlready();
    }

}