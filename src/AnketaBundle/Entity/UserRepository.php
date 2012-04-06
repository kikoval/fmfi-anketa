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

    public function anonymizeAnswersByUser($user, $season) {
        $q = $this->getEntityManager()->createQueryBuilder()
                                      ->update('AnketaBundle\Entity\Answer', 'a')
                                      ->set('a.author', ':nobody')
                                      ->where('a.author = :user AND a.season = :season')
                                      ->getQuery();
        $q->setParameters(array(
            'nobody' => null,
            'user' => $user,
            'season' => $season
         ));

        //TODO(majak): nikde som nenasiel, co tato funkcia vrati, ked to failne
        //             normalne tu vracia pocet updatnutych riadkov
        return $q->execute();
    }

    public function getNumberOfVoters($season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT COUNT(us.id) as voters
                                   FROM AnketaBundle\Entity\UserSeason us
                                   WHERE us.isStudent = true
                                   AND us.season = :season");
        $query->setParameter('season', $season);
        $result = $query->getResult();
        return $result[0]['voters'];
    }

    /**
     * Pocet ludi co anonymizovali.
     * Warning: toto je nasty hack
     * TODO: potrebujeme specialny field k user-season ci anonymizoval
     */
    public function getNumberOfAnonymizations($season) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT COUNT(us.id) as anon
                                   FROM AnketaBundle\Entity\UserSeason us
                                   WHERE us.isStudent = true
                                   AND us.finished = true
                                   AND us.season = :season");
        $query->setParameter('season', $season);
        $result = $query->getResult();
        return $result[0]['anon'];
    }

}
