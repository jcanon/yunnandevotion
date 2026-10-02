<?php

namespace Database\Seeders;

use App\Models\InterfaceTranslation;
use Illuminate\Database\Seeder;

class AccountTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = [
            'invite.add' => ['Add user', '添加用户'], 'invite.accept' => ['Accept invitation', '接受邀请'],
            'invite.mail' => ['You are invited to Yunnan Devotional Atlas. Choose your password using this link within seven days.', '您受邀加入云南信仰空间图谱。请在七天内通过此链接设置密码。'],
            'invite.help' => ['The recipient chooses a password through a single-use email link. The invitation expires after seven days.', '收件人通过一次性邮件链接设置密码。邀请将在七天后过期。'],
            'invite.send' => ['Send invitation', '发送邀请'], 'invite.sent' => ['Invitation created and handed to the configured mail service.', '邀请已创建并交给已配置的邮件服务。'],
            'invite.pending' => ['Pending invitations', '待接受的邀请'], 'invite.resend' => ['Resend invitation', '重新发送邀请'],
            'invite.revoke' => ['Revoke invitation', '撤销邀请'], 'invite.revoked' => ['Invitation revoked', '邀请已撤销'],
            'invite.expired' => ['Expired', '已过期'], 'invite.waiting' => ['Awaiting acceptance', '等待接受'],
            'invite.invalid' => ['This invitation is expired, revoked, or already used. Ask the administrator for help.', '此邀请已过期、已撤销或已使用。请联系管理员。'],
            'invite.duplicate' => ['An account or invitation already exists for this email. Manage the existing entry.', '此邮箱已有账户或邀请。请管理现有记录。'],
            'profile.save' => ['Save profile', '保存资料'], 'profile.saved' => ['Profile saved.', '资料已保存。'],
            'profile.bio-en' => ['Biography — English', '个人简介 — 英文'], 'profile.bio-zh' => ['Biography — Simplified Chinese', '个人简介 — 简体中文'],
            'profile.language' => ['Preferred language', '首选语言'], 'profile.credit' => ['Default public credit', '默认公开署名'],
            'profile.anonymous' => ['Anonymous contributor', '匿名贡献者'],
            'profile.credit-help' => ['This sets the default for future contributions. Moderators can still see your account identity.', '此设置将用于今后的贡献。审核员仍可查看您的账户身份。'],
            'profile.email-help' => ['Email and account permissions are not changed by this form.', '此表单不会更改邮箱或账户权限。'],
            'error.410' => ['This invitation is no longer available.', '此邀请已失效。'],
            'error.409' => ['This entry has changed. Reload the page before continuing.', '此记录已更改。请重新加载页面后继续。'],
            'users' => ['Users & roles', '用户与角色'],
            'users.saved' => ['Account permissions saved.', '账户权限已保存。'],
            'users.save' => ['Save account', '保存账户'],
            'users.next' => ['Next page', '下一页'], 'users.previous' => ['Previous page', '上一页'],
            'users.help' => ['Manage registered accounts. Suspending access preserves their historical contributions.', '管理已注册账户。暂停访问权限会保留其历史贡献。'],
            'users.active' => ['Active', '启用'], 'users.suspended' => ['Suspended', '已停用'],
            'users.verified' => ['Email verified', '邮箱已验证'], 'users.unverified' => ['Email not verified', '邮箱未验证'],
            'error.role' => ['Choose a valid role and account status.', '请选择有效的角色和账户状态。'],
            'error.last-admin' => ['Keep at least one active, verified administrator.', '必须保留至少一位已启用且邮箱已验证的管理员。'],
            'login' => ['Sign in', '登录'], 'register' => ['Create an account', '创建账户'],
            'logout' => ['Sign out', '退出登录'], 'profile' => ['Your profile', '个人资料'],
            'name' => ['Display name', '显示名称'], 'email' => ['Email address', '电子邮箱'],
            'password' => ['Password', '密码'], 'confirmation' => ['Confirm password', '确认密码'],
            'password.help' => ['Use 12–128 characters.', '请使用12至128个字符。'],
            'forgot' => ['Forgot your password?', '忘记密码？'], 'reset' => ['Reset password', '重置密码'],
            'reset.send' => ['Send reset link', '发送重置链接'],
            'reset.sent' => ['If an eligible account matches, a reset link has been sent.', '如果存在符合条件的账户，重置链接已发送。'],
            'reset.done' => ['Password reset. Sign in with your new password.', '密码已重置。请使用新密码登录。'],
            'reset.invalid' => ['This reset link is invalid or expired. Request a new link.', '重置链接无效或已过期。请申请新链接。'],
            'verify' => ['Verify your email', '验证邮箱'],
            'verify.help' => ['Open the verification link sent to your email to access your account.', '请打开邮件中的验证链接，以访问您的账户。'],
            'verify.resend' => ['Resend verification email', '重新发送验证邮件'],
            'verify.sent' => ['Verification email sent.', '验证邮件已发送。'],
            'admin' => ['Administration', '后台管理'], 'moderation' => ['Review desk', '审核工作台'],
            'workspace.pending' => ['Access is enabled. Archive management features are being built.', '访问权限已启用。档案管理功能正在开发中。'],
            'account.intro' => ['Help preserve the history of everyday devotional spaces.', '共同保存日常信仰空间的历史。'],
            'role' => ['Role', '角色'], 'role.contributor' => ['Contributor', '贡献者'],
            'role.moderator' => ['Moderator', '审核员'], 'role.admin' => ['Administrator', '管理员'],
            'error.required' => ['Complete this field.', '请填写此项。'],
            'error.email' => ['Enter a valid email address.', '请输入有效的电子邮箱。'],
            'error.password' => ['Use at least 12 characters for your password.', '密码至少需要12个字符。'],
            'error.long' => ['This value exceeds the allowed length.', '内容超过允许的长度。'],
            'error.confirm' => ['The passwords do not match.', '两次密码不一致。'],
            'error.duplicate' => ['This email is already registered. Sign in or reset your password.', '此邮箱已注册。请登录或重置密码。'],
            'error.login' => ['Unable to sign in with these credentials.', '无法使用这些凭据登录。'],
            'error.heading' => ['Please check the form.', '请检查表单。'],
            'mail.ignore' => ['If you did not request this, you can ignore this message.', '如果您未发起此请求，请忽略此邮件。'],
            'mail.reset' => ['Use this link to choose a new password. It expires in 60 minutes.', '请通过此链接设置新密码。链接在60分钟后过期。'],
            'mail.verify' => ['Verify your email to contribute to Yunnan Devotional Atlas. This link expires in 60 minutes.', '请验证邮箱以参与云南信仰空间图谱。链接在60分钟后过期。'],
            'error.403' => ['You do not have access, or this link is invalid.', '您没有访问权限，或此链接无效。'],
            'error.419' => ['Your session expired. Reload the form and try again.', '会话已过期。请重新加载表单并重试。'],
            'error.429' => ['Too many attempts. Please wait a minute and try again.', '尝试次数过多。请稍候一分钟再试。'],
            'home.link' => ['The project', '项目介绍'], 'skip' => ['Skip to content', '跳转至内容'],
        ];
        foreach ($pairs as $key => [$en, $zh]) {
            foreach (['en' => $en, 'zh-Hans' => $zh] as $locale => $value) {
                InterfaceTranslation::firstOrCreate(compact('key', 'locale'), compact('value'));
            }
        }
    }
}
