<?php


namespace Umino\Anime\Shikimori;


class Roles extends Screenshots
{
    protected static function rebase(array $fields): array
    {
        $objects = [];

        foreach ($fields as $key => $values) {

            if (empty($values['CHARACTER'])) {
                $class = People::getClass();
                $id = $values['PERSON']['ID'];
            } else {
                $class = Characters::getClass();
                $id = $values['CHARACTER']['ID'];
            }

            $objects[$class][$key] = $id;

            unset($fields[$key]['CHARACTER'], $fields[$key]['PERSON']);
        }

        /** @var People|Characters $class */
        foreach ($objects as $class => $ids) {
            $results = $class::creates($ids);
            foreach ($results as $key => $result) {
                $fields[$key]['ENTITY'] = $result;
            }
        }


        $result = [];

        foreach ($fields as $values) {
            /** @var People|Characters $object */
            $object = $values['ENTITY'];

            foreach ($values['ROLES'] as $key => $roleName) {
                $id = static::buildId($roleName, $object->getId());
                $xmlId = static::buildXmlId($roleName, $object->getId());

                $role = new Role($id, $xmlId);
                $role = Role::create($id, [
                    'NAME' => $values['ROLES_RUSSIAN'][$key] ?: $roleName,
                    'NAME_ORIGIN' => $role,
                    'ENTITY' => $object,
                ]);

                $result[$id] = Role::create($id, [
                    'NAME' => $values['ROLES_RUSSIAN'][$key] ?: $roleName,
                    'NAME_ORIGIN' => $role,
                    'ENTITY' => $object,
                ]);
            }
        }

        return $result;
    }

    public static function creates(array $ids): array
    {
        static::addLoad($ids);
        $results = static::load();

        $persons = [];
        foreach ($results as $items) {
            foreach ($items as $item) {

                if ($item['CHARACTER']['ID']) {
                    $personId = $item['CHARACTER']['ID'];
                    $class = Characters::class;
                } else {
                    $personId = $item['PERSON']['ID'];
                    $class = People::class;
                }


                foreach ($item['ROLES_RUSSIAN'] as $key => $roleName) {
                    $persons[$class][] = [
                        'XML_ID' => static::buildXmlId($roleName,$personId),
                        'NAME' => $roleName,
                        'NAME_ORIGIN' => $item['ROLES'][$key],
                        'PERSON_ID' => $personId,
                    ];
                }
            }
        }

        /** @var People|Characters $class */
        foreach ($persons as $class => $roles) {

            $person = $class::creates(array_column($roles, 'PERSON_ID'));
pre($person);die;
            foreach ($roles as $role) {

            }

            pre($person);

            die;
        }




        die;
        $result = [];

        $items = [];

        foreach ($ids as $key => $id) {
            $id = static::buildId($id);
            $xmlId = static::buildXmlId($id, static::getClass());

            if ($item = static::getById($xmlId)) {
                $result[$xmlId] = $item;
                unset($ids[$key]);
            } else {
                $items[$id] = $xmlId;
            }
        }

        if ($loads = static::loadFromDataBase($items)) {
            $result = array_merge($result, $loads);
            foreach (array_keys($loads) as $xmlId) {
                unset($items[array_search($xmlId, $items)]);
            }
        }


        static::addLoad($items);
        $results = static::load();

        foreach ($results as $item) {
            $fields = static::rebase($item);

            $fields['XML_ID'] = static::buildXmlId($item['ID'], static::getClass());
            $fields['CODE'] = static::buildCode($item['ID'], $fields['NAME']);

            static::saveToDataBase($fields);
        }

        if ($loads = static::loadFromDataBase($items)) {
            $result = array_merge($result, $loads);
            foreach (array_keys($loads) as $xmlId) {
                unset($items[array_search($xmlId, $items)]);
            }
        }

        return $result;
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }
}