<?php
$profileImage = trim((string) (current_user()['profile_image'] ?? ''));
if ($profileImage !== ''):
?>
  <img src="<?= htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8') ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
<?php else: ?>
  <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
    <i class="bx bxs-shield-alt-2" aria-hidden="true"></i>
  <?php else: ?>
    👩‍🏫
  <?php endif; ?>
<?php endif; ?>