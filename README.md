# booked-lmu

```
# start dev VM
vagrant up

#
# Encrypting secrets
#
# database passwords
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example group_vars/dev/vault.yml
ansible-vault encrypt --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example group_vars/test/vault.yml

# get versioned roles
./get_roles.sh

# install on dev VM
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/dev install.yml

# install on test server
ansible-playbook --vault-password-file=~/.ansible_vault_passes/bookedscheduler-deploy-example -i inventory/test install.yml --become --ask-become-pass
```
