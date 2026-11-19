<?php
    class Validator {

        public static function isDate(string $value): bool {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

            return $date !== false && $date->format('Y-m-d') === $value;
        }

        public static function normalizeMoney(string $value): ?string {
            $value = trim(str_replace(['R$', ' '], '', $value));

            if (str_contains($value, ',')) {
                $value = str_replace(['.', ','], ['', '.'], $value);
            }

            if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value)) {
                return null;
            }

            if ((float) $value <= 0) {
                return null;
            }

            return number_format((float) $value, 2, '.', '');
        }
    }
