# meta developer: @yourusername
# meta banner: https://raw.githubusercontent.com/hikariatama/hikka-plugins/master/assets/alwayson.jpg

from telethon.tl.functions.account import UpdateStatusRequest
from telethon.tl.functions.contacts import GetContactsRequest
from .. import loader, utils
import asyncio

@loader.tds
class AlwaysOnlineMod(loader.Module):
    """Всегда в онлайне"""

    strings = {
        "name": "AlwaysOnline",
        "enabled": "✅ Модуль включён: ты теперь всегда онлайн!",
        "disabled": "❌ Модуль выключен: ты теперь офлайн.",
    }

    def __init__(self):
        self._task = None
        self.config = loader.ModuleConfig(
            loader.ConfigValue(
                "enabled", False, lambda: "Включить автоподдержку онлайн-статуса"
            )
        )

    async def client_ready(self, client, db):
        if self.config["enabled"]:
            self._task = asyncio.create_task(self.always_online_loop(client))

    async def always_online_loop(self, client):
        while True:
            try:
                await client(UpdateStatusRequest(offline=False))
                await client(GetContactsRequest(0))  # для активности
            except Exception:
                pass
            await asyncio.sleep(60)  # раз в минуту

    @loader.command()
    async def alwayson(self, message):
        """Включает или выключает постоянный онлайн"""
        if self._task:
            self._task.cancel()
            self._task = None
            self.config["enabled"] = False
            await message.edit(self.strings("disabled"))
        else:
            self._task = asyncio.create_task(self.always_online_loop(message.client))
            self.config["enabled"] = True
            await message.edit(self.strings("enabled"))