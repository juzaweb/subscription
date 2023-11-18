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

abstract class ResultEntity
{
    public static function make(...$params): static
    {
        return new static(...$params);
    }
}
