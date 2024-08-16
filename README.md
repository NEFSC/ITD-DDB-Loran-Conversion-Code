# Loran-Conversion-Code

PHP classes implementing the algorithms for converting LORAN-C coordinates to Lat/Lon

# About LORAN-C

>The Loran system is a radio aid to navigation which utilizes  
the principle of hyperbolic fixing. The locus of points for which  
the difference in arrival time of synchronized signals from a pair  
of transmitters is constant determines a hyperbolic line of  
positions. The intersection of two hyperbolic lines of position  
from two pairs of stations determines a hyperbolic fix.

NOTE: LORAN-C has been deprecated: no LORAN towers now exist. However, many fishermen continue to refer to LORAN coordinates because it is a coordinate system they are accustomed to using to identify locations at sea.

## More Information

More resources on this topic can be found by using the search feature on this site.  
<https://discover.dtic.mil/>

## Some Advice for Developers

The code in this repository was derived from the algorithms and BASIC computer programs that you will find in the documents listed below. Translating this information into working, modern code was extremely challenging and you may find it impractical to revisit that process. You may find the information in these texts useful, however, to better understand the principles involved.

### There are two 'gotchas' to look out for if you choose to write your own code based on the information in these texts. 
1. The table of LORAN towers used by the sample BASIC programs use an arcane format for LAT and LON coordinates. They appear to be decimal, but they are actually the Degrees followed by a concatenated string of the Minutes, and decimal Seconds, so 36 40 44.221 is represented as 36.4044221 (note that the tower coordinates in our chainModel.php are in standard format so that they can be updated or edited without this complexity)

2. The second quirk stems from the fact that these texts assume that all points being located are in the region of North America, and consequently a positive longitude indicates a longitude west of the prime meridian, which is the opposite of the norm in which points west of the prime meridian are designated as negative. This was likely done for convenience, but it can cause a lot of confusion 

### How to add tower chains
If you find that you need more tower chain definitions than are included in this repository, you can simply add them into the JSON in chainModel.php (the coordinates in chainModel.php use standard format, rather than the proprietary format described in 'gotcha' number 1 above). You should be able to find historical lists of these LORAN-C towers by searching <https://discover.dtic.mil/>. Remember, when referencing these texts, to identify whether any coordinates in decimal format are truly in decimal format, and whether positive longitudes are actually West or East of the prime meridian.

## Documents Referenced in Developing NOAA's Loran Conversion Application

* Name: *AN ALGORITHM FOR POSITION DETERMINATION USING LORAN-C TRIPLETS WITH A BASIC PROGRAM FOR THE COMMODORE 2001 MICROCOMPUTER*  
Date: March 1980  
Link: [ADA086790.pdf](https://discover.dtic.mil/results/?q=ADA086790.pdf)

* Name: *POSITION DETERMINATION WITH LORAN-C TRIPLETS AND THE HEWLETT-PACKARD HP-41CV PROGRAMMABLE CALCULATOR*  
Date: September 1982  
Link: [ADA122499.pdf](https://discover.dtic.mil/results/?q=ADA122499.pdf)

* Name: *APPLICATION OF ADDITIONAL SECONDARY FACTORIS TO LORAN-C POSITIONS FOR HYDROGRAPHIC OPERATIONS*  
Date: October 1982  
Link: [ADA125620.pdf](https://discover.dtic.mil/results/?q=ADA125620.pdf)

* Name: *MIT RAD LAB SERIES*  
Date: 1947 - 1953  
Link: [Vol 4 LORAN](https://archive.org/details/mit-rad-lab-series-version-2/VOL_4_Loran)

### Acronyms

* TD: Time Delay OR Time Difference
* ITD: Indicated Time Delay
* GRI: Group Repetition Interval

