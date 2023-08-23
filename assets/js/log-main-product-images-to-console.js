jQuery(document).ready(function ($) {
  // Access main product image data passed from PHP
  if (typeof mainProductImageData !== "undefined") {
    console.log("Main Product Image Data:", mainProductImageData);

    // You can loop through the main product image data and log specific details
    mainProductImageData.forEach(function (image) {
      console.log("Product ID:", image.product_id);
      console.log("Main Image ID:", image.image_id);
      console.log("Main Image URL:", image.image_url);
      console.log("Main Image Alt Text:", image.image_alt);
      // Log more fields as needed
    });
  }
});
