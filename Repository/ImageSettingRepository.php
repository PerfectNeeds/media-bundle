<?php

namespace PN\MediaBundle\Repository;

use PN\ServiceBundle\Utils\SQL;
use PN\ServiceBundle\Utils\Validate;

/**
 * ImageSettingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageSettingRepository extends \Doctrine\ORM\EntityRepository {

    public function findByEntity($entity) {
        $entityName = $entity;
        if (is_object($entity)) {
            $entityName = (new \ReflectionClass($entity))->getShortName();
        }
        $imageSetting = $this->findOneByEntityName($entityName);

        if (!$imageSetting) {
            throw new \Exception("Can't find ImageSetting");
        }

        return $imageSetting;
    }

    public function filter($search, $count = FALSE, $startLimit = NULL, $endLimit = NULL) {

        $sortSQL = [
            0 => 's.entity_name',
        ];
        $connection = $this->getEntityManager()->getConnection();
        $where = FALSE;
        $clause = '';

        $searchFiltered = new \stdClass();
        foreach ($search as $key => $value) {
            if (Validate::not_null($value) AND ! is_array($value)) {
                $searchFiltered->{$key} = substr($connection->quote($value), 1, -1);
            } else {
                $searchFiltered->{$key} = $value;
            }
        }


        if (isset($searchFiltered->string) AND $searchFiltered->string) {

            if (SQL::validateSS($searchFiltered->string)) {
                $where = ($where) ? ' AND ( ' : ' WHERE ( ';
                $clause .= SQL::searchSCG($searchFiltered->string, 's.id', $where);
                $clause .= SQL::searchSCG($searchFiltered->string, 's.entity_name', ' OR ');
                $clause .= " ) ";
            }
        }

        if ($count) {
            $sqlInner = "SELECT count(s.id) as `count` FROM image_setting s ";

            $statement = $connection->prepare($sqlInner);
            $statement->execute();
            return $queryResult = $statement->fetchColumn();
        }
//----------------------------------------------------------------------------------------------------------------------------------------------------
        $sql = "SELECT s.id FROM image_setting s";
        $sql .= $clause;

        if (isset($searchFiltered->ordr) AND Validate::not_null($searchFiltered->ordr)) {
            $dir = $searchFiltered->ordr['dir'];
            $columnNumber = $searchFiltered->ordr['column'];
            if (isset($columnNumber) AND array_key_exists($columnNumber, $sortSQL)) {
                $sql .= " ORDER BY " . $sortSQL[$columnNumber] . " $dir";
            }
        } else {
            $sql .= ' ORDER BY s.id DESC';
        }


        if ($startLimit !== NULL AND $endLimit !== NULL) {
            $sql .= " LIMIT " . $startLimit . ", " . $endLimit;
        }

        $statement = $connection->prepare($sql);
        $statement->execute();
        $filterResult = $statement->fetchAll();
        $result = array();

        foreach ($filterResult as $key => $r) {
            $result[] = $this->find($r['id']);
        }
//-----------------------------------------------------------------------------------------------------------------------
        return $result;
    }

}
