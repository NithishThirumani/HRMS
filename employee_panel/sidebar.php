  <!-- Nav Item - esignature -->
  <li class="nav-item">
    <a class="nav-link <?php echo $document_collapsed; ?>" href="#" data-toggle="collapse"
        data-target="#collapseEsignature" aria-expanded="<?php echo $document_show ? 'true' : 'false'; ?>"
        aria-controls="collapseEsignature">
        <i class="fas fa-fw fa-file-signature"></i>
        <span>E-Signature</span>
    </a>
    <div id="collapseEsignature" class="collapse <?php echo $document_show; ?>"
        aria-labelledby="headingEsignature" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">E-Signature Controls:</h6>
            <a class="collapse-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"
                href="/emps/esignature/index.php">
                <i class="fas fa-fw fa-home fa-sm"></i> Dashboard
            </a>
            <a class="collapse-item <?php echo ($current_page == 'pending.php') ? 'active' : ''; ?>"
                href="/emps/esignature/pending.php">
                <i class="fas fa-fw fa-clock fa-sm"></i> Pending Documents
            </a>
            <a class="collapse-item <?php echo ($current_page == 'signed.php') ? 'active' : ''; ?>"
                href="/emps/esignature/signed.php">
                <i class="fas fa-fw fa-check-circle fa-sm"></i> Signed Documents
            </a>
        </div>
    </div>
  </li>

  <!-- Nav Item - Salary --> 