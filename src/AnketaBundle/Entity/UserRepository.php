<?php
/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage Anketa__Entity__Repository
 * @author     Jakub Markoš <jakub.markos@gmail.com>
 */

namespace AnketaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Repository class for User Entity
 */

class UserRepository extends EntityRepository {

    public function findOneWithRolesByUserName($username)
    {
        $q = $this->createQueryBuilder('u')
                ->leftJoin('u.roles', 'r')
                ->where('u.userName = :username')
                ->getQuery();
        $q->setParameter('username', $username);

        $result = $q->execute();

        if (count($result) > 1) {
            throw new NonUniqueResultException;
        }
        return array_shift($result);
    }

    public function anonymizeAnswersByUserId($userId) {
        $q = $this->getEntityManager()->createQueryBuilder()
                                      ->update('AnketaBundle\Entity\Answer', 'a')
                                      ->set('a.author', '?1')
                                      ->where('a.author = ?2')
                                      ->getQuery();
        $q->setParameters(array(
            1 => null,
            2 => $userId
         ));

        //TODO(majak): nikde som nenasiel, co tato funkcia vrati, ked to failne
        //             normalne tu vracia pocet updatnutych riadkov
        return $q->execute();
    }

    public function getNumberOfVoters() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT COUNT(u.id) as voters
                                   FROM AnketaBundle\Entity\User u
                                   WHERE u.participated = true");
        $result = $query->getResult();
        return $result[0]['voters'];
    }

    /**
     * Pocet ludi co anonymizovali.
     * Warning: toto je nasty hack
     * TODO: potrebujeme specialny field k user-season ci anonymizoval
     */
    public function getNumberOfAnonymizations() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT COUNT(u.id) as anon
                                   FROM AnketaBundle\\Entity\\User u
                                   JOIN u.roles r
                                   WHERE u.hasVote = 0 AND 'ROLE_AIS_STUDENT' = r.name
                                   ");
        $result = $query->getResult();
        return $result[0]['anon'];
    }

    public function getStudyProgrammes($userId) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT DISTINCT(sp.id)
                           FROM AnketaBundle\\Entity\\UsersSubjects us
                           JOIN us.studyProgram sp
                           WHERE us.user = :userId
                           ORDER BY sp.code ASC");
        $query->setParameter('userId', $userId);

        $helper = function($item){return $item['id'];};
        return array_map($helper, $query->getArrayResult());
    }

}
