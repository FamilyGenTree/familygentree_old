<?php
// Controller for the advanced search page
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009 PGV Development Team. All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Controller_AdvancedSearch extends WT_Controller_Search {
	var $fields = array();
	var $values = array();
	var $plusminus = array();
	var $errors = array();

	function __construct() {
		parent::__construct();

		$this->setPageTitle(WT_I18N::translate('Advanced search'));
		
		if (empty($_REQUEST['action'])) {
			$this->action="advanced";
		}
		if ($this->action=="advanced") {
			if (isset($_REQUEST['fields'])) {
				$this->fields = $_REQUEST['fields'];
				ksort($this->fields);
			}
			if (isset($_REQUEST['values'])) {
				$this->values = $_REQUEST['values'];
			}
			if (isset($_REQUEST['plusminus'])) {
				$this->plusminus = $_REQUEST['plusminus'];
			}
			$this->reorderFields();
			$this->advancedSearch();
		}
		if (!$this->fields) {
			$this->fields=array(
				'NAME:GIVN:SDX',
				'NAME:SURN:SDX',
				'BIRT:DATE',
				'BIRT:PLAC',
				'FAMS:MARR:DATE',
				'FAMS:MARR:PLAC',
				'DEAT:DATE',
				'DEAT:PLAC',
				'FAMC:HUSB:NAME:GIVN:SDX',
				'FAMC:HUSB:NAME:SURN:SDX',
				'FAMC:WIFE:NAME:GIVN:SDX',
				'FAMC:WIFE:NAME:SURN:SDX',
			);
		}
	}

	function getOtherFields() {
		$ofields = array(
			'ADDR','ADDR:CITY','ADDR:STAE','ADDR:CTRY','ADDR:POST',
			'ADOP:DATE','ADOP:PLAC',
			'AFN',
			'BAPL:DATE','BAPL:PLAC',
			'BAPM:DATE','BAPM:PLAC',
			'BARM:DATE','BARM:PLAC',
			'BASM:DATE','BASM:PLAC',
			'BLES:DATE','BLES:PLAC',
			'BURI:DATE','BURI:PLAC',
			'CAST',
			'CENS:DATE','CENS:PLAC',
			'CHAN:DATE', 'CHAN:_WT_USER',
			'CHR:DATE','CHR:PLAC',
			'CREM:DATE','CREM:PLAC',
			'DSCR',
			'EMAIL',
			'EMIG:DATE','EMIG:PLAC',
			'ENDL:DATE','ENDL:PLAC',
			'EVEN',
			'EVEN:DATE','EVEN:PLAC',
			'FAMS:CENS:DATE','FAMS:CENS:PLAC',
			'FAMS:DIV:DATE','FAMS:DIV:PLAC',
			'FAMS:NOTE',
			'FAMS:SLGS:DATE','FAMS:SLGS:PLAC',
			'FAX',
			'FCOM:DATE','FCOM:PLAC',
			'IMMI:DATE','IMMI:PLAC',
			'NAME:NICK','NAME:_MARNM','NAME:_HEB','NAME:ROMN',
			'NATI',
			'NATU:DATE','NATU:PLAC',
			'NOTE',
			'OCCU',
			'ORDN:DATE','ORDN:PLAC',
			'RELI',
			'RESI','RESI:DATE','RESI:PLAC',
			'SLGC:DATE','SLGC:PLAC',
			'TITL',
			'_BRTM:DATE','_BRTM:PLAC',
			'_MILI',
		);
		// Allow (some of) the user-specified fields to be selected
		foreach (explode(',', get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD')) as $fact) {
			if (
				$fact!='BIRT' &&
				$fact!='DEAT' &&
				$fact!='ASSO' &&
				!in_array($fact, $ofields) &&
				!in_array("{$fact}:DATE", $ofields) &&
				!in_array("{$fact}:PLAC", $ofields)
			) {
				$ofields[]=$fact;
			}
		}
		$fields=array();
		foreach ($ofields as $field) {
			$fields[$field]=WT_Gedcom_Tag::GetLabel($field);
		}
		uksort($fields, array('WT_Controller_AdvancedSearch', 'tagSort'));
		return $fields;
	}

	public static function tagSort($x, $y) {
		list($x1)=explode(':', $x.':');
		list($y1)=explode(':', $y.':');
		$tmp=utf8_strcasecmp(WT_Gedcom_Tag::getLabel($x1), WT_Gedcom_Tag::getLabel($y1));
		if ($tmp) {
			return $tmp;
		} else {
			return utf8_strcasecmp(WT_Gedcom_Tag::getLabel($x), WT_Gedcom_Tag::getLabel($y));
		}
	}

	function getValue($i) {
		$val = "";
		if (isset($this->values[$i])) $val = $this->values[$i];
		return $val;
	}

	function getField($i) {
		$val = "";
		if (isset($this->fields[$i])) $val = htmlentities($this->fields[$i]);
		return $val;
	}

	function getIndex($field) {
		return array_search($field, $this->fields);
	}

	function getLabel($tag) {
		return WT_Gedcom_Tag::getLabel(str_replace(':SDX', '', $tag));
	}

	function reorderFields() {
		$i = 0;
		$newfields = array();
		$newvalues = array();
		$newplus = array();
		$rels = array();
		foreach ($this->fields as $j=>$field) {
			if (strpos($this->fields[$j], "FAMC:HUSB:NAME")===0 || strpos($this->fields[$j], "FAMC:WIFE:NAME")===0) {
				$rels[$this->fields[$j]] = $this->values[$j];
				continue;
			}
			$newfields[$i] = $this->fields[$j];
			if (isset($this->values[$j])) $newvalues[$i] = $this->values[$j];
			if (isset($this->plusminus[$j])) $newplus[$i] = $this->plusminus[$j];
			$i++;
		}
		$this->fields = $newfields;
		$this->values = $newvalues;
		$this->plusminus = $newplus;
		foreach ($rels as $field=>$value) {
			$this->fields[] = $field;
			$this->values[] = $value;
		}
	}

	function advancedSearch($justSql=false, $table="individuals", $prefix="i") {
		DMsoundex("", "opencache");
		$this->myindilist = array ();
		$fct = count($this->fields);
		if ($fct==0) {
			return;
		}

		// Dynamic SQL query, plus bind variables
		$sql="SELECT DISTINCT 'INDI' AS type, ind.i_id AS xref, ind.i_file AS ged_id, ind.i_gedcom AS gedrec FROM `##individuals` ind";
		$bind=array();

		// Join the following tables
		$anything_to_find=false;
		$father_name     =false;
		$mother_name     =false;
		$spouse_family   =false;
		$indi_name       =false;
		$indi_date       =false;
		$fam_date        =false;
		$indi_plac       =false;
		$fam_plac        =false;
		foreach ($this->fields as $n=>$field) {
			if ($this->values[$n]) {
				$anything_to_find=true;
				if (substr($field, 0, 14)=='FAMC:HUSB:NAME') {
					$father_name=true;
				} elseif (substr($field, 0, 14)=='FAMC:WIFE:NAME') {
					$mother_name=true;
				} elseif (substr($field, 0, 4)=='FAMS') {
					$spouse_family=true;
				} elseif (substr($field, 0, 4)=='NAME') {
					$indi_name=true;
				} elseif (strpos($field, ':DATE')!==false) {
					if (substr($field, 0, 4)=='MARR') {
						$fam_date=true;
						$spouse_family=true;
					} else {
						$indi_date=true;
					}
				} elseif (strpos($field, ':PLAC')!==false) {
					if (substr($field, 0, 4)=='MARR') {
						$fam_plac=true;
						$spouse_family=true;
					} else {
						$indi_plac=true;
					}
				}
			}
		}

		if ($father_name || $mother_name) {
			$sql.=" JOIN `##link`   l_1 ON (l_1.l_file=? AND l_1.l_from=ind.i_id AND l_1.l_type='FAMC')";
			$bind[]=WT_GED_ID;
		}
		if ($father_name) {
			$sql.=" JOIN `##link`   l_2 ON (l_2.l_file=? AND l_2.l_from=l_1.l_to AND l_2.l_type='HUSB')";
			$sql.=" JOIN `##name`   f_n ON (f_n.n_file=? AND f_n.n_id  =l_2.l_to)";
			$bind[]=WT_GED_ID;
			$bind[]=WT_GED_ID;
		}
		if ($mother_name) {
			$sql.=" JOIN `##link`   l_3 ON (l_3.l_file=? AND l_3.l_from=l_1.l_to AND l_3.l_type='WIFE')";
			$sql.=" JOIN `##name`   m_n ON (m_n.n_file=? AND m_n.n_id  =l_3.l_to)";
			$bind[]=WT_GED_ID;
			$bind[]=WT_GED_ID;
		}
		if ($spouse_family) {
			$sql.=" JOIN `##link`   l_4 ON (l_4.l_file=? AND l_4.l_from=ind.4_id AND l_4.l_type='FAMS')";
			$sql.=" JOIN `##family` fam ON (fam.n_file=? AND fam.f_id  =l_4.l_to)";
			$bind[]=WT_GED_ID;
			$bind[]=WT_GED_ID;
		}
		if ($indi_name) {
			$sql.=" JOIN `##name`   i_n ON (i_n.n_file=? AND i_n.n_id=ind.i_id)";
			$bind[]=WT_GED_ID;
		}
		if ($indi_date) {
			$sql.=" JOIN `##dates`  i_d ON (i_d.d_file=? AND i_d.d_gid=ind.i_id)";
			$bind[]=WT_GED_ID;
		}
		if ($fam_date) {
			$sql.=" JOIN `##date`   f_d ON (f_d.d_file=? AND f_d.d_id=fam.f_id)";
			$bind[]=WT_GED_ID;
		}
		if ($indi_plac) {
			$sql.=" JOIN `##places`       i_p  ON (i_p.p_file  =? AND i_p.p_id   =ind.i_id)";
			$sql.=" JOIN `##placelinks`   i_pl ON (i_pl.pl_file=? AND i_pl.pl_gid=i_p.p_id)";
			$bind[]=WT_GED_ID;
			$bind[]=WT_GED_ID;
		}
		if ($fam_plac) {
			$sql.=" JOIN `##places`       f_p  ON (f_p.p_file  =? AND f_p.p_id   =fam.f_id)";
			$sql.=" JOIN `##placelinks`   f_pl ON (f_pl.pl_file=? AND f_pl.pl_gid=f_p.p_id)";
			$bind[]=WT_GED_ID;
			$bind[]=WT_GED_ID;
		}

		// Add the where clause
		$sql.=" WHERE ind.i_file=?";
		$bind[]=WT_GED_ID;
		for ($i=0; $i<$fct; $i++) {
			$field = $this->fields[$i];
			$value = $this->values[$i];
			if ($value==='') continue;
			$parts = preg_split("/:/", $field.'::::');
			if ($parts[0]=='NAME') {
				// NAME:*
				switch ($parts[1]) {
				case 'GIVN':
					switch ($parts[2]) {
					case 'EXACT':
						$sql.=" AND i_n.n_givn=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql.=" AND i_n.n_givn LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql.=" AND i_n.n_givn LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx=explode(':', soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_givn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx=explode(':', soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_givn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				case 'SURN':
					switch ($parts[2]) {
					case 'EXACT':
						$sql.=" AND i_n.n_surname=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql.=" AND i_n.n_surname LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql.=" AND i_n.n_surname LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx=explode(':', soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=" AND (".implode(' OR ', $sdx).")";
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx=explode(':', soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=" AND (".implode(' OR ', $sdx).")";
						break;
					}
					break;
				case 'NICK':
				case '_MARNM':
				case '_HEB':
				case '_AKA':
					$sql.=" AND i_n.n_type=? AND i_n.n_full LIKE CONCAT('%', ?, '%')";
					$bind[]=$parts[1];
					$bind[]=$value;
					break;
				}
			} elseif ($parts[1]=='DATE') {
				// *:DATE
				$date = new WT_Date($value);
				if ($date->isOK()) {
					$jd1 = $date->date1->minJD;
					if ($date->date2) $jd2 = $date->date2->maxJD;
					else $jd2 = $date->date1->maxJD;
					if (!empty($this->plusminus[$i])) {
						$adjd = $this->plusminus[$i]*365;
						//echo $jd1.":".$jd2.":".$adjd;
						$jd1 = $jd1 - $adjd;
						$jd2 = $jd2 + $adjd;
					}
					$sql.=" AND i_d.d_fact=? AND i_d.d_julianday1>=? AND i_d.d_julianday2<=?";
					$bind[]=$parts[0];
					$bind[]=$jd1;
					$bind[]=$jd2;
				}
			} elseif ($parts[0]=='FAMS' && $parts[2]=='DATE') {
				// FAMS:*:DATE
				$date = new WT_Date($value);
				if ($date->isOK()) {
					$jd1 = $date->date1->minJD;
					if ($date->date2) $jd2 = $date->date2->maxJD;
					else $jd2 = $date->date1->maxJD;
					if (!empty($this->plusminus[$i])) {
						$adjd = $this->plusminus[$i]*365;
						//echo $jd1.":".$jd2.":".$adjd;
						$jd1 = $jd1 - $adjd;
						$jd2 = $jd2 + $adjd;
					}
					$sql.=" AND f_d.d_type=? AND f_d.d_julianday1>=? AND f_d.d_julianday2<=?";
					$bind[]=$parts[1];
					$bind[]=$jd1;
					$bind[]=$jd2;
				}
			} elseif ($parts[1]=='PLAC') {
				// *:DATE
				// SQL can only link a place to a person/family, not to an event.
				$places = preg_split("/[, ]+/", $value);
				foreach ($places as $place) {
					$sql.=" AND (i_p.p_place=?";
					$bind[]=$place;
					foreach (DMsoundex($place) as $sdx) {
						$sql.=" OR i_p.p_dm_soundex LIKE CONCAT('%', ?, '%')";
						$bind[]=$sdx;
					}
				}
				$sql.= ")";
			} elseif ($parts[0]=='FAMS' && $parts[2]=='PLAC') {
				// *:DATE
				// SQL can only link a place to a person/family, not to an event.
				$places = preg_split("/[, ]+/", $value);
				foreach ($places as $place) {
					$sql.=" AND (f_p.p_place=?";
					$bind[]=$place;
					foreach (DMsoundex($place) as $sdx) {
						$sql.=" OR f_p.p_dm_soundex LIKE CONCAT('%', ?, '%')";
						$bind[]=$sdx;
					}
				}
				$sql.= ")";
			} elseif ($parts[0]=='FAMC' && $parts[2]=='NAME') {
				$table=$parts[1]=='HUSB' ? 'f_n' : 'm_n';
				// NAME:*
				switch ($parts[3]) {
				case 'GIVN':
					switch ($parts[4]) {
					case 'EXACT':
						$sql.=" AND {$table}.n_givn=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql.=" AND {$table}.n_givn LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql.=" AND {$table}.n_givn LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx=explode(':', soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_givn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx=explode(':', soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_givn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				case 'SURN':
					switch ($parts[4]) {
					case 'EXACT':
						$sql.=" AND {$table}.n_surname=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql.=" AND {$table}.n_surname LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql.=" AND {$table}.n_surname LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx=explode(':', soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx=explode(':', soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				}
			} elseif ($parts[0]=='FAMS') {
				$sql.=" AND fam.f_gedcom LIKE CONCAT('%', ?, '%')";
				$bind[]=$value;
			} else {
				$sql.=" AND ind.i_gedcom LIKE CONCAT('%', ?, '%')";
				$bind[]=$value;
			}
		}
		$rows=WT_DB::prepare($sql)->execute($bind)->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			$this->myindilist[]=WT_Person::getInstance($row);
		}
	}

	function PrintResults() {
		require_once WT_ROOT.'includes/functions/functions_print_lists.php';
		if ($this->myindilist) {
			uasort($this->myindilist, array('WT_GedcomRecord', 'Compare'));
			echo format_indi_table($this->myindilist);
			return true;
		} else {
			if ($this->isPostBack) {
				echo '<p class="ui-state-highlight">', WT_I18N::translate('No results found.'), '</p>';
			}
			return false;
		}
	}
}