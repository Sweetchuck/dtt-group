<?php

declare(strict_types = 1);

namespace Sweetchuck\DrupalTestTraits\Group\Behat\Context;

use Annertech\Griffith\Tests\Utils;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Sweetchuck\DrupalTestTraits\Core\Behat\Context\Base;
use Sweetchuck\DrupalTestTraits\Core\Behat\Context\Entity as EntityContext;
use Sweetchuck\DrupalTestTraits\Group\GroupTrait;

class Group extends Base {

  use GroupTrait;

  /**
   * @Given groups:
   *
   * @code
   * Given groups:
   *  | type  | my_bundle_01   |
   *  | label | Behat group 01 |
   *  | uid   | behat_admin    |
   * @endcode
   */
  public function doGroupCreate(TableNode $table) {
    $fieldValues = $table->getRowsHash();

    $entityContext = $this->getContext(EntityContext::class);
    $entityContext->createContentEntity('group', $fieldValues);
  }

  /**
   * @Given :username is member in the :groupLabel group
   * @Given :username is member in the :groupLabel group with :groupRoles role(s)
   *
   * @code
   * Given "Emma" is member in the "My group 01" group
   * @endcode
   *
   * @code
   * Given "Emma" is member in the "My group 01" group with "article_author,article_editor" roles
   * @endcode
   */
  public function doGroupMemberAdd(string $groupLabel, string $username, string $groupRoles = '') {
    $entityContext = $this->getContext(EntityContext::class);

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entityContext->getEntityByLabel('group', $groupLabel);
    Assert::assertNotNull($group, "group '$groupLabel' does not exists");

    /** @var \Drupal\user\UserInterface $user */
    $user = $entityContext->getEntityByLabel('user', $username);
    Assert::assertNotNull($user, "user '$username' does not exists");

    $membershipValues = [];
    foreach (Utils::explode(',', $groupRoles) as $groupRoleName) {
      $membershipValues['group_roles'][] = sprintf(
        '%s-%s',
        $group->bundle(),
        $groupRoleName,
      );
    }

    $group->addMember($user, $membershipValues);
  }

  /**
   * @Given add :groupRoles role(s) to :username in :groupLabel group
   */
  public function doGroupMemberRoleAdd(
    string $groupLabel,
    string $username,
    string $groupRoles
  ) {
    $entityContext = $this->getContext(EntityContext::class);

    $group = $entityContext->getEntityByLabel('group', $groupLabel);
    Assert::assertNotNull($group, "group '$groupLabel' does not exists");

    $user = $entityContext->getEntityByLabel('user', $username);
    Assert::assertNotNull($group, "user '$username' does not exists");

    /** @var \Drupal\group\GroupMembership $membership */
    $membership = $group->getMember($user);
    Assert::assertNotEmpty($membership, "user {$username} is not a member in '$groupLabel' group");

    throw new \Exception('@todo https://www.drupal.org/project/group/issues/3132084');
  }

  /**
   * @code
   * Given users are members in :group group:
   *   | name     | roles        |
   *   | MyUser01 | admin        |
   *   | MyUser02 | editor       |
   *   | MyUser03 | admin,editor |
   * @endcode
   */
  public function doGroupAddMembers() {
  }

  /**
   * @Given Content :nodeLabel has a relationship with :groupLabel group
   */
  public function doGroupRelationshipCreateNode(string $groupLabel, string $nodeLabel) {
    $entityContext = $this->getContext(EntityContext::class);
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $entityContext->getEntityByLabel('group', $groupLabel);
    $node = $entityContext->getEntityByLabel('node', $nodeLabel);

    $values = [];
    $group->addRelationship(
      $node,
      'group_node:' . $node->bundle(),
      $values,
    );
  }

}
