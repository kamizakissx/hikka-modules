from .. import loader, utils
from telethon import events
import datetime

@loader.tds
class UsernameLoggerMod(loader.Module):
    """Логирует изменения username и имени пользователей"""
    strings = {"name": "NameLogger"}

    async def client_ready(self, client, db):
        self.db = db
        self.client = client
        if not self.db.get("namelogger", "history"):
            self.db.set("namelogger", "history", {})

        client.add_event_handler(self.track_usernames, events.NewMessage)

    async def track_usernames(self, event):
        user = await event.get_sender()
        if not user or not user.username:
            return
        
        uid = str(user.id)
        history = self.db.get("namelogger", "history")
        if uid not in history:
            history[uid] = []

        current_data = {"username": user.username, "name": user.first_name, "time": datetime.datetime.now().isoformat()}

        if not history[uid] or history[uid][-1]["username"] != user.username or history[uid][-1]["name"] != user.first_name:
            history[uid].append(current_data)
            self.db.set("namelogger", "history", history)

    @loader.command()
    async def namelog(self, message):
        """Показать историю ников и username: .namelog @user"""
        args = utils.get_args_raw(message)
        reply = await message.get_reply_message()

        if args:
            user = await self.client.get_entity(args)
        elif reply:
            user = await reply.get_sender()
        else:
            user = await message.get_sender()

        uid = str(user.id)
        history = self.db.get("namelogger", "history", {}).get(uid)

        if not history:
            await message.edit("История не найдена.")
            return

        out = f"🧾 История имени @{user.username or 'нет username'}:\n\n"
        for item in history:
            time = item["time"].split("T")[0]
            out += f"📅 {time}: {item['name']} — @{item['username']}\n"

        await message.edit(out)
