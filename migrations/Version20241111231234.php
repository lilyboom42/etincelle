<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241111231234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01DF675F31B');
        $this->addSql('ALTER TABLE blog_post_blog_post_category DROP FOREIGN KEY FK_2645A9CD8A053C2B');
        $this->addSql('ALTER TABLE blog_post_blog_post_category DROP FOREIGN KEY FK_2645A9CDA77FBEAF');
        $this->addSql('ALTER TABLE blog_post_category_relation DROP FOREIGN KEY FK_D4F224F312469DE2');
        $this->addSql('ALTER TABLE blog_post_category_relation DROP FOREIGN KEY FK_D4F224F3A77FBEAF');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE blog_post_blog_post_category');
        $this->addSql('DROP TABLE blog_post_category');
        $this->addSql('DROP TABLE blog_post_category_relation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BA5AE01DF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE blog_post_blog_post_category (blog_post_id INT NOT NULL, blog_post_category_id INT NOT NULL, INDEX IDX_2645A9CDA77FBEAF (blog_post_id), INDEX IDX_2645A9CD8A053C2B (blog_post_category_id), PRIMARY KEY(blog_post_id, blog_post_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE blog_post_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_CA275A0C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE blog_post_category_relation (id INT AUTO_INCREMENT NOT NULL, blog_post_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_D4F224F3A77FBEAF (blog_post_id), INDEX IDX_D4F224F312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE blog_post_blog_post_category ADD CONSTRAINT FK_2645A9CD8A053C2B FOREIGN KEY (blog_post_category_id) REFERENCES blog_post_category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_blog_post_category ADD CONSTRAINT FK_2645A9CDA77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_category_relation ADD CONSTRAINT FK_D4F224F312469DE2 FOREIGN KEY (category_id) REFERENCES blog_post_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE blog_post_category_relation ADD CONSTRAINT FK_D4F224F3A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
