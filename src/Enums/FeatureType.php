<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Enums;

enum FeatureType: string
{
    case BOOLEAN = 'boolean';
    case SIZE = 'size';
    case NUMBER = 'number';
    case TEXT = 'text';
}
