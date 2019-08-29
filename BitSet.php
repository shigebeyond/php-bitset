<?php

/**
 * 比特操作类
 * @author shijianhang
 * @date 2019-8-26
 */
class BitSet{

    /**
     * 每个word的比特位数
     */
    const WORD_BITS = 31;

    /**
     * int数组, 用来存储比特位
     * @var array
     */
    private $words = array();

    /**
     * 根据int数组来实例化
     * @param array $words
     * @return BitSet
     */
    public static function from_int_array(array $words = array(0)){
        $bs = new BitSet();
        $bs->words = $words;
        return $bs;
    }

    /**
     * 根据二进制数据(字符串)来实例化
     * @param $bin 二进制数据(字符串), 支持空字符串
     * @return BitSet
     */
    public static function from_binary_data($bin){
        // unpack()得到的int数组的key从1开始, 而不是从0开始
        $arr = unpack('V*', $bin);
        // 转为从0开始
        $words = array();
        $nword = count($arr);
        for($i = 0; $i < $nword; $i++){
            $words[$i] = $arr[$i + 1];
        }
        return static::from_int_array($words);
    }

    /**
     * 转为int数组
     * @return array
     */
    public function to_int_array(){
        return $this->words;
    }

    /**
     * 转为二进制数据(字符串)
     *    如果 $this->words 是空数组, 则返回空字符串
     * @return string
     */
    public function to_binary_string(){
        // pack('V*', 1, 2, 4, 8); // 第二个参数开始是不定参数
        $params = $this->words;
        array_unshift($params, 'V*');
        return call_user_func_array('pack', $params);
    }

    /**
     * 转为比特位的数组
     * @return
     */
    public function to_bit_indexs(){
        $result = array();
        foreach($this->words as $iword => $word){
            for($ibit = 0; $ibit <= static::WORD_BITS; $ibit++){
                if($word & (1 << $ibit))
                    $result[] = $iword * static::WORD_BITS + $ibit;
            }
        }
        return $result;
    }

    /**
     * 设置多个比特位为1
     * @param $ibits 指定比特位
     */
    public function sets(array $ibits){
        foreach ($ibits as $ibit)
            $this->set($ibit);
    }

    /**
     * 设置指定比特位为1
     * @param $ibit 指定比特位
     */
    public function set($ibit){
        if (!is_numeric($ibit) || $ibit < 0)
            throw new Exception('Argument [$ibit] must be a positive integer');

        // 获得词的位置
        $iword = intval($ibit / static::WORD_BITS);

        // 扩展词
        $this->expand_words($iword);

        // 对词进行位合并
        $this->words[$iword]  = $this->words[$iword] | (1 << ($ibit % static::WORD_BITS));
    }

    /**
     * 清空指定比特位
     * @param $ibit 指定比特位
     */
    public function clear($ibit){
        if (!is_numeric($ibit) || $ibit < 0)
            throw new Exception('Argument [$ibit] must be a positive integer');

        // 获得词的位置
        $iword = intval($ibit / static::WORD_BITS);

        $nword = count($this->words); // 现有的词数
        if($iword > $nword)
            return;

        // 对词进行位操作
        $this->words[$iword]  = $this->words[$iword] & (1 << ~($ibit % static::WORD_BITS));
    }

    /**
     * 扩展词
     * @param $iword
     */
    protected function expand_words($iword) {
        $required = $iword + 1; // 需要的词数
        $nword = count($this->words); // 现有的词数
        if($nword < $required){
            for($i = $nword; $i < $required; $i++){
                $this->words[$i] = 0;
            }
        }
    }

    /**
     * 判断指定比特是否为1
     * @param $ibit
     * @return bool
     */
    public function get($ibit){
        if ($ibit < 0)
            return false;

        // 获得词的位置
        $iword = intval($ibit / static::WORD_BITS);

        $nword = count($this->words);
        if ($nword < $iword)
            return false;

        return (bool)( $this->words[$iword]
            & (1 << ($ibit % static::WORD_BITS)));
    }

    /**
     * and操作
     * @param $set
     */
    public function ands(BitSet $set) {
        if ($this == $set)
            return;

        $required = count($set->words); // 待合并的词数
        $this->expand_words($required);

        // Perform logical AND on words in common
        for($i = 0; $i < $required; $i++) {
            $this->words[$i] &= $set->words[$i];
        }
    }

    /**
     * or操作
     * @param $set
     */
    public function ors(BitSet $set) {
        if ($this == $set)
            return;

        $required = count($set->words); // 待合并的词数
        $this->expand_words($required);

        // Perform logical OR on words in common
        for($i = 0; $i < $required; $i++) {
            $this->words[$i] |= $set->words[$i];
        }
    }
}

/*
// int序列转为二进制数据(字符串)
$bin = pack('V*', 1, 2, 4);
// 二进制数据(字符串)转为int数组 -- unpack()得到的int数组的key从1开始, 而不是从0开始
$arr = unpack('V*', $bin);
echo $bin;
*/

/*
$bs = new BitSet();
// 设置指定比特位
$bs->set(0); // 1
$bs->set(1); // 2
$bs->set(2); // 4
$bs->set(32); //
// 转为二进制数据(字符串)
$bin = $bs->to_binary_string();
echo $bin;
echo "\n";
// 根据二进制数据(字符串)来实例化
$bs2 = BitSet::from_binary_data($bin);
// 读比特位
echo $bs->get(1);
echo "\n";
echo $bs->get(2);
echo "\n";
echo $bs->get(3);
echo "\n";
// 转为int数组
var_dump($bs2->to_int_array());
// 获得所有比特位
var_dump($bs2->to_bit_indexs());
// 清空指定比特位
$bs2->clear(32);
echo $bs2->get(2);
echo "\n";
echo $bs2->get(32);
echo "\n";
var_dump($bs2->to_int_array());
var_dump($bs2->to_bit_indexs());*/
