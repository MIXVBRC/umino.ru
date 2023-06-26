<?php


namespace Umino\Anime\Shikimori;


class Roles extends Entity
{
    protected static bool $md5Id = true;

    protected function rebase(array $fields): array
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
            $results = $class::getByIds($ids);
            foreach ($results as $key => $result) {
                $fields[$key]['ENTITY'] = $result;
            }
        }

        $result = [];

        foreach ($fields as $values) {
            /** @var People|Characters $object */
            $object = $values['ENTITY'];

            foreach ($values['ROLES'] as $key => $role) {
                $id = static::buildId($role, $object->getId());
                $result[$id] = Role::create($id, [
                    'NAME' => $values['ROLES_RUSSIAN'][$key] ?: $role,
                    'NAME_ORIGIN' => $role,
                    'ENTITY' => $object,
                ]);
            }
        }

        return $result;
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }
}