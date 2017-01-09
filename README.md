# bookedscheduler-deploy-example

This project shows how to use Ansible for setting up Booked Scheduler.

Managing secrets
----------------

[Ansible Vault](http://docs.ansible.com/ansible/playbooks_vault.html) is used to store database passwords.

Create new vault password file.
```
mkdir ~/.ansible_vault_passes
ansible-vault --new-vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example
```
Secrets are named with prefix `vault_`.
```
$ grep -r vault_
inventory/group_vars/dev/vars.yml:  mysql_root_password: "{{ vault_mysql_root_password }}"
inventory/group_vars/dev/vars.yml:  booked_db_password: "{{ vault_booked_db_password }}"
inventory/group_vars/test/vars.yml:  mysql_root_password: "{{ vault_mysql_root_password }}"
inventory/group_vars/test/vars.yml:  booked_db_password: "{{ vault_booked_db_password }}"
```

Best practice is to have `vars.yml` and `vault.yml` files in each subdirectory of group_vars.
Edit `vault.yml` to contain the actual secrets.
```
$ cat inventory/group_vars/dev/vault.yml
---
  vault_mysql_root_password: "big_secret_here"
  booked_db_password: "another_secret_here"
  booked_install_password: "and_another_one_here"
```
Then encrypt the `vault.yml` files.
```
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example group_vars/dev/vault.yml
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example group_vars/test/vault.yml
```

Start test VM and install
-------------------------
```
# start dev VM
vagrant up

# get versioned roles
ansible-galaxy install -f -r requirements.yml -p ./roles

# install on dev VM
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/dev install.yml
```
Install on test server
----------------------
```
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/test install.yml --become --ask-become-pass
```
