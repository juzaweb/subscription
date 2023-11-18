<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Support\Entities;

class CreatedPlanResult extends ResultEntity
{
    protected array $metas = [];

    public function __construct(public string $id)
    {
    }

    public function getMetas(): array
    {
        return $this->metas;
    }

    public function setMeta(array $metas): static
    {
        $this->metas = $metas;

        return $this;
    }
}
