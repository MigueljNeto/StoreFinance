<?php
    class Users {
        public function __construct (
            public string $name,
            public string $email,
            public bool $active = true,
            public ?int $id = null,
            public ?string $createdAt = null,
            public ?string $password = null 
        ) {}
    }
