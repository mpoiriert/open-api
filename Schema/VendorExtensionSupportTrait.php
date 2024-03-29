<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;

trait VendorExtensionSupportTrait
{
    #[JMS\Type('array<string,'.MixedData::class.'>')]
    #[JMS\Accessor(getter: 'getFakeVendor')]
    public ?array $vendor = [];

    public function getFakeVendor()
    {
        return null;
    }

    public function getVendorData(): ?array
    {
        return $this->vendor;
    }

    public function getVendorDataKey($key, $default = null)
    {
        return $this->vendor[$key] ?? $default;
    }

    public function setVendorData(?array $data = null): void
    {
        $this->vendor = $data;
    }

    public function setVendorDataKey($key, $data): void
    {
        $this->vendor[$key] = $data;
    }

    public function removeVendorDataKey($key): void
    {
        unset($this->vendor[$key]);
    }
}
