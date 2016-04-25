import inject


class Logger:
    pass

class ErrorLogger(Logger):
    def err(self):
        pass

class WarnLogger(Logger):
    def warn(self):
        pass

class Engine:
    def __init__(self, currencies):
        pass

class MemoryEngine(Engine):
    def save(self):
        pass

class MysqlEngine(Engine):
    def save(self):
        pass

class Currency:
    @staticmethod
    def currencies():
        return ['coins']

class Session:
    def __init__(self, user_id, engine):
        pass

class Wallet:
    pass

class CreditTransaction:
    def __init__(self, session, currencies, logger):
        pass
    def deposit(self, amount):
        print "deposited %s" % (amount,)


def create_engine():
    currencies = Currency.currencies()
    return MemoryEngine(currencies)

def create_logger():
    return WarnLogger()

def create_session():
    return Session(12, create_engine())

def create_wallet():
    currencies = Currency.currencies()
    logger = inject.instance(Logger)
    session = inject.instance(Session)
    return CreditTransaction(session, currencies, logger)


def config(binder):
    binder.bind_to_provider(Engine, create_engine)
    binder.bind_to_provider(Logger, create_logger)
    binder.bind_to_provider(Session, create_session)
    binder.bind_to_provider(Wallet, create_wallet)


inject.configure(config)

logger = inject.instance(Logger)
logger.warn()

wallet = inject.instance(Wallet)
wallet.deposit(10)
