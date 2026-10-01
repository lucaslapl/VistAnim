-- Base dédiée aux tests automatisés (phpunit.mysql.xml)
CREATE DATABASE IF NOT EXISTS natureanim_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON natureanim_testing.* TO 'natureanim'@'%';
