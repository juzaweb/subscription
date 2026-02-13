<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Entities;

use Juzaweb\Modules\Subscription\Enums\FeatureType;

class Feature
{
    public string $label;
    public FeatureType $type;
    public ?string $description = null;

    public $formatter = null;

    public function __construct(public string $name, protected array $options = [])
    {
        $this->label = $options['label'] ?? title_from_key($name);
        $this->type = $options['type'] ?? FeatureType::BOOLEAN;
        $this->description = $options['description'] ?? null;
        $this->formatter = $options['formatter'] ?? null;
    }

    public function getLabelWithValue($value)
    {
        if (isset($this->formatter)) {
            return call_user_func($this->formatter, $value);
        }

        if (!$value) {
            return $this->label;
        }

        if ($this->type === FeatureType::BOOLEAN) {
            return $this->label;
        }

        if ($this->type === FeatureType::NUMBER) {
            $value = number_format($value);
        }

        if ($this->type === FeatureType::SIZE) {
            $value = format_size_units($value * 1024 * 1024);
        }

        return sprintf('%s %s', $value, $this->label);
    }
}
