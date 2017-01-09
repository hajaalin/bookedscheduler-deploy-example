# bookedscheduler-deploy-example

This project shows how to install [Booked] (https://www.bookedscheduler.com) with Ansible
 (see also https://github.com/hajaalin/ansible-role-bookedscheduler).
The way to set up a test VM with Vagrant is copied from http://hakunin.com/six-ansible-practices.

Prerequisites
-------------
- [Ansible](https://www.ansible.com/)
- [Vagrant](https://www.vagrantup.com/)

Download roles
--------------
```
ansible-galaxy install -f -r requirements.yml -p ./roles

```

Managing secrets
----------------

[Ansible Vault](http://docs.ansible.com/ansible/playbooks_vault.html) is used to store database passwords.

Create new vault password file. If you don't have openssl installed, see [alternatives](http://www.howtogeek.com/howto/30184/10-ways-to-generate-a-random-password-from-the-command-line/).
```
mkdir ~/.ansible_vault_passes
openssl rand -base64 32 > ~/.ansible_vault_passes/bookedscheduler-deploy-example
chmod 0400 /home/hajaalin/GitHub/bookedscheduler-deploy-example/README.md
```
Best practice is to have `vars.yml` and `vault.yml` files in each subdirectory of group_vars.
Edit `vault.yml` to define the actual secret variables. Use prefix `vault_`.
```
$ cat inventory/group_vars/dev/vault.yml
---
  vault_mysql_root_password: "big_secret_here"
  vault_booked_db_password: "another_secret_here"
  vault_booked_install_password: "and_another_one_here"
```
In `vars.yml`, the secrets are assigned to role variables.
```
$ head inventory/group_vars/dev/vars.yml
---
  # secrets from vault.yml
  mysql_root_password: "{{ vault_mysql_root_password }}"
  booked_db_password: "{{ vault_booked_db_password }}"
  booked_install_password: "{{ vault_booked_install_password }}"
```
Then encrypt the `vault.yml` files.
```
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example inventory/group_vars/dev/vault.yml
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example inventory/group_vars/test/vault.yml
```
If you don't like to store the vault password in plain text, you can create and store it e.g. in KeePassX,
and use option `--ask-vault-pass` instead.

Start test VM and install
-------------------------
```
# start dev VM
vagrant up

# install on dev VM
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/dev install.yml
```
Install on test server
----------------------
```
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/test install.yml --become --ask-become-pass
```
