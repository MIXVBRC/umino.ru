<?php


namespace Umino\Anime\Shikimori;

// TODO: доделать
class Roles extends Genres
{
    protected function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['ROLES_RUSSIAN'] ?: $fields['ROLES'],
            'NAME_ORIGIN' => $fields['ROLES'],
            'ENTITY' => $fields['ENTITY'],
        ];
    }

//    protected static function load(int $id): array
//    {
//        if (empty(self::$collection)) {
//            $results = Request::getResponse(self::getUrl());
//            foreach ($results as $result) {
//                self::$collection[$result['ID']] = $result;
//            }
//        }
//
//        return self::$collection[$id];
//    }

//    public static function getCollection(array $ids): array
//    {
//        $result = [];
//
//        $entity = [];
//
//        foreach ($roles as $role) {
//            if ($role['PERSON']) {
//                $entity['PERSONS'][] = $role['PERSON']['ID'];
//            }
//            if ($role['CHARACTER']) {
//                $entity['CHARACTERS'][] = $role['CHARACTER']['ID'];
//            }
//        }
//
//        $entity['PERSONS'] = People::getCollection($entity['PERSONS']);
//        $entity['CHARACTERS'] = Characters::getCollection($entity['CHARACTERS']);
//
//        foreach ($roles as $role) {
//
//            if (empty($role)) continue;
//
//            if ($role['CHARACTER']['ID']) {
//                $role['ENTITY'] = $entity['CHARACTERS'][$role['CHARACTER']['ID']];
//            } else if ($role['PERSON']['ID']) {
//                $role['ENTITY'] = $entity['PERSONS'][$role['PERSON']['ID']];
//            } else {
//                continue;
//            }
//
//            unset($role['CHARACTER']);
//            unset($role['PERSON']);
//
//            $result[] = new Role($role);
//        }
//
//        return $result;
//    }

//    public static function getCollection(array $data): array
//    {
//        $result = [];
//        $collection = [];
//
//        foreach ($data as $id) {
//            if ($object = self::getById($id)) {
//                $result[$id] = $object;
//            } else {
//                $collection[$id] = self::load($id);
//            }
//        }
//
//        $class = self::getClass();
//        foreach ($collection as $id => $fields) {
//            $result[$id] = new $class((int) $id, $fields);
//        }
//
//        return $result;
//    }
}