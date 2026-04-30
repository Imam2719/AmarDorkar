
<?php $__env->startSection('title', app()->getLocale()==='bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management'); ?>
<?php $__env->startSection('page-title', app()->getLocale()==='bn' ? 'ক্যাটাগরি ব্যবস্থাপনা' : 'Category Management'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

  
  <div class="col-lg-4">
    <div class="card animate-fadeInUp">
      <div class="card-header">
        <h5 class="card-title bengali-text mb-0">
          <i class="fas fa-plus-circle me-2 text-success"></i>
          <?php echo e(app()->getLocale()==='bn' ? 'নতুন ক্যাটাগরি' : 'Add New Category'); ?>

        </h5>
      </div>
      <div class="card-body">
        <form method="POST" action="<?php echo e(route('admin.categories.store')); ?>">
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label class="form-label bengali-text">
              <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (বাংলা) *' : 'Category Name (Bengali) *'); ?>

            </label>
            <input type="text"
                   class="form-control <?php $__errorArgs = ['name_bn'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   name="name_bn"
                   value="<?php echo e(old('name_bn')); ?>"
                   placeholder="যেমন: পোশাক ও পরিধান"
                   required>
            <?php $__errorArgs = ['name_bn'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">
              <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (English) *' : 'Category Name (English) *'); ?>

            </label>
            <input type="text"
                   class="form-control <?php $__errorArgs = ['name_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   name="name_en"
                   value="<?php echo e(old('name_en')); ?>"
                   placeholder="e.g. Clothing & Fashion"
                   required>
            <?php $__errorArgs = ['name_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-3">
            <label class="form-label">
              <?php echo e(app()->getLocale()==='bn' ? 'আইকন (Font Awesome class)' : 'Icon (Font Awesome class)'); ?>

            </label>
            <div class="input-group">
              <span class="input-group-text" id="iconPreview"><i class="fas fa-tag"></i></span>
              <input type="text"
                     class="form-control"
                     name="icon"
                     id="iconInput"
                     value="<?php echo e(old('icon', 'fas fa-tag')); ?>"
                     placeholder="fas fa-tag"
                     oninput="updateIconPreview(this.value)">
            </div>
            <div class="form-text">
              <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons ↗</a>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">
              <?php echo e(app()->getLocale()==='bn' ? 'রঙ' : 'Color'); ?>

            </label>
            <div class="input-group">
              <input type="color"
                     class="form-control form-control-color"
                     name="color"
                     value="<?php echo e(old('color', '#1a9e4a')); ?>"
                     style="max-width:60px;">
              <input type="text"
                     class="form-control"
                     name="color_text"
                     value="<?php echo e(old('color', '#1a9e4a')); ?>"
                     placeholder="#1a9e4a"
                     readonly>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">
              <?php echo e(app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order'); ?>

            </label>
            <input type="number"
                   class="form-control"
                   name="sort_order"
                   value="<?php echo e(old('sort_order', 0)); ?>"
                   min="0">
          </div>

          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-plus me-2"></i>
            <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি যোগ করুন' : 'Add Category'); ?>

          </button>
        </form>
      </div>
    </div>
  </div>

  
  <div class="col-lg-8">
    <div class="card animate-fadeInUp">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title bengali-text mb-0">
          <i class="fas fa-list me-2"></i>
          <?php echo e(app()->getLocale()==='bn' ? 'সকল ক্যাটাগরি' : 'All Categories'); ?>

          <span class="badge bg-primary ms-2"><?php echo e($categories->count()); ?></span>
        </h5>
      </div>
      <div class="card-body p-0">
        <?php if($categories->isEmpty()): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
            <p class="bengali-text"><?php echo e(app()->getLocale()==='bn' ? 'কোনো ক্যাটাগরি নেই।' : 'No categories found.'); ?></p>
          </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background:var(--bg);">
              <tr>
                <th style="width:50px;">#</th>
                <th><?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি' : 'Category'); ?></th>
                <th class="text-center" style="width:100px;">
                  <?php echo e(app()->getLocale()==='bn' ? 'পণ্য' : 'Products'); ?>

                </th>
                <th class="text-center" style="width:90px;">
                  <?php echo e(app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status'); ?>

                </th>
                <th class="text-center" style="width:140px;">
                  <?php echo e(app()->getLocale()==='bn' ? 'অ্যাকশন' : 'Actions'); ?>

                </th>
              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr id="row<?php echo e($cat->id); ?>">
                <td class="text-muted" style="font-size:13px;"><?php echo e($loop->iteration); ?></td>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:<?php echo e($cat->color ?? '#1a9e4a'); ?>20;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i class="<?php echo e($cat->icon ?? 'fas fa-tag'); ?>" style="color:<?php echo e($cat->color ?? '#1a9e4a'); ?>;font-size:16px;"></i>
                    </div>
                    <div>
                      <div class="fw-semibold bengali-text" style="font-size:14px;"><?php echo e($cat->name_bn); ?></div>
                      <div class="text-muted" style="font-size:12px;"><?php echo e($cat->name_en); ?></div>
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary"><?php echo e($cat->products_count); ?></span>
                </td>
                <td class="text-center">
                  <form method="POST" action="<?php echo e(route('admin.categories.toggle', $cat)); ?>" class="d-inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button type="submit"
                            class="badge border-0 <?php echo e($cat->status ? 'bg-success' : 'bg-danger'); ?>"
                            style="cursor:pointer;font-size:11px;padding:5px 10px;"
                            title="<?php echo e($cat->status ? 'Deactivate' : 'Activate'); ?>">
                      <?php echo e($cat->status
                          ? (app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active')
                          : (app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive')); ?>

                    </button>
                  </form>
                </td>
                <td class="text-center">
                  <div class="d-flex gap-1 justify-content-center">
                    
                    <button class="btn btn-outline-primary btn-sm"
                            onclick="openEditModal(<?php echo e($cat->id); ?>, '<?php echo e(addslashes($cat->name_bn)); ?>', '<?php echo e(addslashes($cat->name_en)); ?>', '<?php echo e($cat->icon); ?>', '<?php echo e($cat->color); ?>', <?php echo e($cat->sort_order); ?>)"
                            title="<?php echo e(app()->getLocale()==='bn' ? 'সম্পাদনা' : 'Edit'); ?>">
                      <i class="fas fa-edit"></i>
                    </button>

                    
                    <?php if($cat->products_count == 0): ?>
                    <form method="POST" action="<?php echo e(route('admin.categories.destroy', $cat)); ?>" class="d-inline"
                          onsubmit="return confirm('<?php echo e(app()->getLocale()==='bn' ? 'এই ক্যাটাগরি ডিলিট করবেন?' : 'Delete this category?'); ?>')">
                      <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                      <button class="btn btn-outline-danger btn-sm" title="<?php echo e(app()->getLocale()==='bn' ? 'ডিলিট' : 'Delete'); ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-outline-secondary btn-sm" disabled
                            title="<?php echo e(app()->getLocale()==='bn' ? 'পণ্য আছে, ডিলিট করা যাবে না' : 'Has products, cannot delete'); ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title bengali-text">
          <i class="fas fa-edit me-2"></i>
          <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি সম্পাদনা' : 'Edit Category'); ?>

        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="editForm">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label bengali-text">
              <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (বাংলা) *' : 'Category Name (Bengali) *'); ?>

            </label>
            <input type="text" class="form-control" name="name_bn" id="edit_name_bn" required>
          </div>

          <div class="mb-3">
            <label class="form-label">
              <?php echo e(app()->getLocale()==='bn' ? 'ক্যাটাগরি নাম (English) *' : 'Category Name (English) *'); ?>

            </label>
            <input type="text" class="form-control" name="name_en" id="edit_name_en" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><?php echo e(app()->getLocale()==='bn' ? 'আইকন' : 'Icon'); ?></label>
            <div class="input-group">
              <span class="input-group-text" id="editIconPreview"><i class="fas fa-tag"></i></span>
              <input type="text" class="form-control" name="icon" id="edit_icon"
                     oninput="updateEditIconPreview(this.value)">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label"><?php echo e(app()->getLocale()==='bn' ? 'রঙ' : 'Color'); ?></label>
            <input type="color" class="form-control form-control-color w-100" name="color" id="edit_color">
          </div>

          <div class="mb-3">
            <label class="form-label"><?php echo e(app()->getLocale()==='bn' ? 'সাজানোর ক্রম' : 'Sort Order'); ?></label>
            <input type="number" class="form-control" name="sort_order" id="edit_sort_order" min="0">
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?php echo e(app()->getLocale()==='bn' ? 'বাতিল' : 'Cancel'); ?>

          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>
            <?php echo e(app()->getLocale()==='bn' ? 'আপডেট করুন' : 'Update'); ?>

          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Icon preview on add form
function updateIconPreview(val) {
    const el = document.getElementById('iconPreview');
    if (el) el.innerHTML = `<i class="${val || 'fas fa-tag'}"></i>`;
}

// Sync color picker with text input
document.querySelector('input[type="color"]')?.addEventListener('input', function() {
    const txt = document.querySelector('input[name="color_text"]');
    if (txt) txt.value = this.value;
});

// Edit modal
function openEditModal(id, name_bn, name_en, icon, color, sort_order) {
    const route = `<?php echo e(url('admin/categories')); ?>/${id}`;
    document.getElementById('editForm').action = route;
    document.getElementById('edit_name_bn').value   = name_bn;
    document.getElementById('edit_name_en').value   = name_en;
    document.getElementById('edit_icon').value      = icon;
    document.getElementById('edit_color').value     = color || '#1a9e4a';
    document.getElementById('edit_sort_order').value= sort_order;
    updateEditIconPreview(icon);
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function updateEditIconPreview(val) {
    const el = document.getElementById('editIconPreview');
    if (el) el.innerHTML = `<i class="${val || 'fas fa-tag'}"></i>`;
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\amardorkar-laravel\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>