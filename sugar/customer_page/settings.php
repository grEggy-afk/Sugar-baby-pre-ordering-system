<?php
// REMOVED: session_start() - Already started in user_dashboard.php
// REMOVED: require_once '../db.php' - Not needed for this UI page

// Get user info from the existing session
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['user_email'] ?? 'customer@sugarbaby.ph';
?>

<!-- INLINE STYLES FOR THIS PAGE ONLY -->
<style>
    /* Dark Mode Variables (Inherited from parent) */
    .settings-big-card {
        background: var(--bg-card);
        border-radius: 20px;
        border: 2px solid var(--pastel-pink-dark);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }

    .avatar-profile-group {
        text-align: center;
        margin: 25px 0;
    }

    #settingsBigAvatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: var(--pastel-yellow);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        font-weight: bold;
        color: #2c3e50;
        margin: 0 auto 10px;
        border: 3px solid var(--pastel-yellow-dark);
    }

    #settingsFullName {
        font-size: 28px;
        font-weight: bold;
        color: var(--text-main);
    }

    .settings-main-card {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: 16px;
        border: 2px solid var(--border);
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
        box-shadow: var(--card-shadow);
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--bg-main);
    }
    .setting-item:last-child { border-bottom: none; }

    .settings-action-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 1rem;
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .settings-action-btn:hover {
        background-color: var(--pastel-pink);
        color: var(--sidebar-active-text);
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--pastel-pink-dark); }
    input:checked + .slider:before { transform: translateX(22px); }

    .settings-card-header {
        border-bottom: 2px solid var(--border);
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .hidden { display: none !important; }
</style>

<!-- SETTINGS VIEW -->
<div class="settings-page-container">
    <h2 style="margin-bottom: 1.5rem; color: var(--text-main);">Settings & Account</h2>
    
    <!-- TOP CONTAINER: Profile -->
    <div class="settings-big-card">

        <!-- Current Active Account -->
        <div class="avatar-profile-group">
            <div id="settingsBigAvatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
            <div id="settingsFullName" style="font-size: 28px; font-weight: bold; color: var(--text-main);"><?php echo htmlspecialchars($user_name); ?></div>
            <div style="font-size: 20px; color: var(--text-muted); margin-top: 5px;">Customer Account</div>
        </div>
    </div>

    <!-- SINGLE MAIN CONTAINER -->
    <div class="settings-main-card">
        <h3 class="settings-card-header">
            <i class="fa-solid fa-gear" style="color: var(--pastel-pink-dark);"></i> Account & System Settings
        </h3>

        <!-- 2-COLUMN LAYOUT INSIDE ONE CONTAINER -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            
            <!-- LEFT: ACCOUNT MANAGEMENT -->
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-circle" style="color: var(--pastel-pink-dark);"></i> Account Management
                </h4>

                <div class="setting-item" style="margin-top:1.5rem;">
                    <div>
                        <strong>Edit Profile</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Name: <span id="exName"><?php echo htmlspecialchars($user_name); ?></span> | Email: <span id="exEmail"><?php echo htmlspecialchars($user_email); ?></span></p>
                    </div>
                    <button class="settings-action-btn" onclick="openModal('editProfile')"><i class="fa-solid fa-chevron-right"></i></button>
                </div>

                <div class="setting-item">
                    <div>
                        <strong>Change Password</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Update login password</p>
                    </div>
                    <button class="settings-action-btn" onclick="openModal('changePassword')"><i class="fa-solid fa-chevron-right"></i></button>
                </div>

                <div class="setting-item">
                    <div>
                        <strong>Delete Account</strong>
                        <p style="font-size: 0.8rem; color: #e53e3e;">Permanently remove account</p>
                    </div>
                    <button class="settings-action-btn" style="color:#e53e3e;" onclick="confirmDelete()"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>

            <!-- RIGHT: APPEARANCE & SYSTEM -->
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-palette" style="color: var(--pastel-yellow-dark);"></i> Appearance & System
                </h4>

                <div class="setting-item">
                    <div>
                        <strong>Dark Mode</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Switch light / dark theme</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="darkModeToggle">
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting-item">
                    <div>
                        <strong>Email Notifications</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">Receive order alerts & updates</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="notifToggle" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting-item">
                    <div>
                        <strong>Language</strong>
                        <p style="font-size: 0.8rem; color: var(--text-muted);">System language</p>
                    </div>
                    <span style="padding: 0.5rem 1rem; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border); color: var(--text-main);">English</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SETTINGS FORM MODAL -->
<div id="settingsModal" class="hidden" style="position: fixed; inset:0; background: rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
    <div style="background:var(--bg-card); border-radius:16px; padding:2rem; width:420px; border:2px solid var(--border);">
        <h3 id="modalTitle" style="margin-bottom:1rem; color:var(--text-main);"></h3>
        <form id="settingsForm">
            <div id="modalFields"></div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
                <button type="button" onclick="closeSettingsModal()" style="padding:0.6rem 1.2rem; border-radius:8px; border:1px solid var(--border); background:transparent; color:var(--text-main);">Cancel</button>
                <button type="submit" style="padding:0.6rem 1.2rem; border-radius:8px; border:none; background:var(--pastel-pink-dark); color:white; font-weight:700;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --------------------------
    // DARK MODE TOGGLE (SYNC ALL SWITCHES)
    // --------------------------
    const darkToggles = document.querySelectorAll('.darkModeToggle');
    darkToggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const isDark = this.checked;
            document.body.classList.toggle('dark-mode', isDark);
            darkToggles.forEach(t => t.checked = isDark);
        });
    });

    // --------------------------
    // MODAL SYSTEM
    // --------------------------
    let activeModalType = '';

    function openModal(type){
        activeModalType = type;
        const modal = document.getElementById('settingsModal');
        const title = document.getElementById('modalTitle');
        const fields = document.getElementById('modalFields');
        fields.innerHTML = '';

        if(type === 'editProfile'){
            title.textContent = 'Edit Profile';
            fields.innerHTML = `
            <div style="margin-bottom:1rem;">
                <label>Full Name</label>
                <input type="text" id="inpName" value="${document.getElementById('exName').textContent}" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
            </div>
            <div style="margin-bottom:1rem;">
                <label>Email Address</label>
                <input type="email" id="inpEmail" value="${document.getElementById('exEmail').textContent}" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
            </div>`;
        }
        if(type === 'changePassword'){
            title.textContent = 'Change Password';
            fields.innerHTML = `
            <div style="margin-bottom:1rem;">
                <label>Current Password</label>
                <input type="password" id="oldPass" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
            </div>
            <div style="margin-bottom:1rem;">
                <label>New Password</label>
                <input type="password" id="newPass" style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid var(--border); background:var(--bg-main); color:var(--text-main);">
            </div>`;
        }
        document.getElementById('settingsModal').classList.remove('hidden');
    }

    function closeSettingsModal(){
        document.getElementById('settingsModal').classList.add('hidden');
        activeModalType = '';
    }

    function confirmDelete(){
        if(confirm("⚠️ Are you sure? This will delete your account permanently!")){
            alert("Account deletion request sent.");
        }
    }

    document.getElementById('settingsForm').addEventListener('submit', function(e){
        e.preventDefault();

        if(activeModalType === 'editProfile'){
            document.getElementById('exName').textContent = document.getElementById('inpName').value;
            document.getElementById('exEmail').textContent = document.getElementById('inpEmail').value;
            
            // Update the big avatar and name display
            const newName = document.getElementById('inpName').value;
            const firstLetter = newName.trim().charAt(0).toUpperCase();
            document.getElementById('settingsBigAvatar').textContent = firstLetter;
            document.getElementById('settingsFullName').textContent = newName;
            
            alert('Profile updated!');
        }
        if(activeModalType === 'changePassword') {
            alert('Password changed successfully!');
        }
        closeSettingsModal();
    });
</script>