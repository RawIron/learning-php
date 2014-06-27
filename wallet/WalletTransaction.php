<?php


abstract class WalletTransaction
{
    protected $_engineMapper = array(
            'self' => array('_getBalance' => '_getSelf',
                            '_updateBalance' => '_updateSelf')
            );
            
    protected $_engine = false;
    
    public function getEngines() {
        return array_keys($_engineMapper);
    }    
    

    protected $_currencies = array('premium', 'coins');
    
    protected $_session = false;
    protected $_log = false;
    
    protected $_accounts = array();
    protected $_currency = '';
    
    public function __construct($session, $log)
    {
        $this->_session = $session;
        $this->_log = $log;
            
        foreach ($this->_currencies as $currency) {
            $this->_accounts[$currency]['balance']  = false;
        }
         
        $this->_reset();
    }
    
    /**
     * clean up transaction data
     */
    protected function _reset()
    {
        foreach ($this->_currencies as $currency) {
            $this->_accounts[$currency]['amountDebits']  = 0;
            $this->_accounts[$currency]['amountCredits'] = 0;
        }
    }    
    
    /**
     * check currency
     */   
    protected function _checkandSetCurrency($currency)
    {
        if (! in_array($currency, $this->_currencies) ) {
            return false;         
        }
        
        $this->_currency = $currency;
        return true;        
    }
    
    /**
     * check amount
     */   
    protected function _checkAmount($amount) {
        if ( $amount < 0 ) {           
            return false;
        }
        return true;        
    }    
    
    public function getAccountBalance($currency) {
        $this->_checkandSetCurrency($currency);
        return $this->_accounts[$this->_currency]['balance'];
    }    
    
    public function getTransactionBalance($currency) {
        $this->_checkandSetCurrency($currency);
        return $this->_accounts[$this->_currency]['amountCredits'] - $this->_accounts[$this->_currency]['amountDebits'];
    }
    
    protected function _checkIfOneBalanceChanged() {
        foreach ($this->_currencies as $currency) {            
            if ($this->getTransactionBalance($currency) != 0) {
                return true;
            }
        }
        return false;     
    }
    
    public function getTransactionCurrencies() {
        return $this->_currencies;
    }

    
    protected function _getSelf() {
        foreach ($this->_currencies as $currency) {
            $balances[$currency] = (int) 0;
        }
        return $balances;        
    }
        
    public function _updateSelf($amounts) {
        $this->_setBalances();
               
        foreach ($this->_currencies as $currency) {
            $this->_accounts[$currency]['balance'] += $amounts[$currency];
        }
    }

    protected function _setBalances() {
        foreach ($this->_currencies as $currency) {
            if ($this->_accounts[$currency]['balance'] === false) {
                $balances = $this->{$this->_engineMapper[$this->_engine]['_getBalance']}();
                break;
            }
        }

        foreach ($this->_currencies as $currency) {
            if ($this->_accounts[$currency]['balance'] === false) {
                $this->_accounts[$currency]['balance'] = $balances[$currency];
            }
        }        
    }
    
    /**
     *  add to credits
     */
    public function add($amount, $currency) {
        if (!$this->_checkAmount($amount) ) {
            return false;
        }
        if (!$this->_checkAndSetCurrency($currency) ) {
            return false;
        }

        $this->_accounts[$this->_currency]['amountCredits'] += $amount;
        return true;        
    }

    
    /**
     *  add to debits
     */
    public function sub($amount, $currency){
        if (!$this->_checkAmount($amount) ) {
            return false;
        }
        if (!$this->_checkAndSetCurrency($currency) ) {         
            return false;
        }
        
        $this->_accounts[$this->_currency]['amountDebits'] += $amount;
        return true;
    }

    
    protected abstract function _getBalance();
    protected abstract function _updateBalance($amounts);
     
    /**
     *  removes/adds amount from funds
     */
    public abstract function commit();
    
    /**
     *  refund
     */    
    public abstract function rollback();

}