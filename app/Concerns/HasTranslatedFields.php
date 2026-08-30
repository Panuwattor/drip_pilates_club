<?php

namespace App\Concerns;

/**
 * ทำให้เรียก $model->name แล้วได้ name_th หรือ name_en ตามภาษาปัจจุบันอัตโนมัติ
 * จะได้ไม่ต้องเขียน if เช็คภาษาทุกที่ใน Blade
 *
 * ใช้งาน: ประกาศ protected array $translatable = ['name', 'description'];
 *
 *   $class->name          // ตามภาษาปัจจุบัน
 *   $class->name_th       // บังคับภาษาไทย
 *   $class->trans('name', 'en')
 */
trait HasTranslatedFields
{
    public function trans(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $locale = in_array($locale, ['th', 'en'], true) ? $locale : 'th';

        $value = $this->getAttribute("{$field}_{$locale}");

        // ถ้าภาษานั้นยังไม่ได้กรอก ให้ fallback ไปอีกภาษา ดีกว่าโชว์ช่องว่าง
        if ($value === null || $value === '') {
            $value = $this->getAttribute("{$field}_" . ($locale === 'th' ? 'en' : 'th'));
        }

        return $value;
    }

    public function getAttribute($key)
    {
        if (
            $key !== null
            && ! array_key_exists($key, $this->attributes)
            && in_array($key, $this->translatable ?? [], true)
        ) {
            return $this->trans($key);
        }

        return parent::getAttribute($key);
    }

    /** ฟิลด์ที่ต้องกรอกครบทั้ง 2 ภาษา ใช้สร้าง validation rules ฝั่งแอดมิน */
    public function translatableColumns(): array
    {
        $columns = [];

        foreach ($this->translatable ?? [] as $field) {
            $columns[] = "{$field}_th";
            $columns[] = "{$field}_en";
        }

        return $columns;
    }
}
