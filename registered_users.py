class RegisteredUsers():

    def __init__(self):
        self.registered_users = {}

    def register(self, user):
        if user.number in self.registered_users:
            return False
        else:
            self.registered_users[user.number] = user
            return True

    def get_user(self, number):
        if number in self.registered_users:
            return self.registered_users[number]
        else:
            return None

    def get_all_users(self):
        return self.registered_users

    def delete_user(self, number):
        if number in self.registered_users:
            del self.registered_users[number]
            return True
        else:
            return False