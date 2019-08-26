BitSet class for PHP

just like java's `java.util.BitSet`

demo

```
$bs = new BitSet();
// set bit
$bs->set(0); // 1
$bs->set(1); // 2
$bs->set(2); // 4
$bs->set(32); //
// transform to binary string
$bin = $bs->to_binary_string();
echo $bin;
echo "\n";

// create an instance from binary string
$bs2 = BitSet::from_binary_data($bin);
// get bit
echo $bs->get(1);
echo "\n";
echo $bs->get(2);
echo "\n";
echo $bs->get(3);
echo "\n";
// transform to int array
var_dump($bs2->to_int_array());
// get all the bit index
var_dump($bs2->to_bit_indexs());
// clear bit
$bs2->clear(32);
echo $bs2->get(2);
echo "\n";
echo $bs2->get(32);
echo "\n";
var_dump($bs2->to_int_array());
var_dump($bs2->to_bit_indexs());
```
