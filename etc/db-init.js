if (db.system.users.find({user: "ixavier_user"}).count() == 0) {
    db.createUser(
        {
            user: "ixavier_user",
            pwd: "ixavier_password_101",
            roles: [{role: "userAdminAnyDatabase", db: "admin"}]
        }
    );
}